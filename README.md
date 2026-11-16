# Mi Catálogo

Catálogo online para un solo emprendimiento (una tienda de ropa, un vivero, un bazar...).
Está hecho con Yii2 basic, MySQL/MariaDB y Bootstrap 5.

El público ve las publicaciones disponibles, puede buscar, filtrar por categoría y ordenar, y también compartir el catálogo o cada producto por WhatsApp, Facebook, copiando el link o con el menú del celular. Al compartir aparece la vista previa con foto, título y descripción, porque el sitio tiene etiquetas Open Graph.
Quien está logueado ve todo el catálogo, con marcas de disponible o no disponible, botones para editar y para habilitar o deshabilitar, y un panel de control.

## Requisitos

- PHP 8.1 o más nuevo, con `gd`, `exif` y `fileinfo`. No necesita `intl`.
- MySQL o MariaDB.
- Apache con `mod_rewrite`.
- Composer.

## Instalación (XAMPP)

1. Copiá el proyecto en `C:\xampp\htdocs\micatalogo` e instalá las dependencias:

   ```bash
   composer install
   ```

2. Creá la base de datos:

   ```sql
   CREATE DATABASE micatalogo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Copiá `.env.example` como `.env` y completalo: datos de la base y una `COOKIE_KEY` larga y al azar. El archivo `.env` nunca se sube al repositorio.

4. Creá las tablas:

   ```bash
   php yii migrate
   ```

5. Creá el primer administrador. Te pide el usuario, el nombre y la contraseña:

   ```bash
   php yii admin/crear
   ```

   Para crear un editor desde la consola: `php yii admin/crear ana "Ana Pérez" --rol=editor`.

6. Abrí `http://localhost/micatalogo/` y entrá con el link **Ingresar** del pie de página.

En un hosting, el proyecto completo va en la carpeta del sitio. El `.htaccess` de la raíz manda todo a `web/`, así que el código, la configuración y `.env` no quedan expuestos.

## Publicar en los sitios de los clientes (InfinityFree u otro hosting con FTP)

Un mismo código sirve para todos los clientes: cada sitio tiene su propio `.env`, su base, sus fotos y su estilo.

### Configuración (una sola vez)

Copiá `deploy/sitios.example.ini` como `deploy/sitios.ini` y agregá un bloque por cliente con los datos de FTP y de la base. Ese archivo tiene contraseñas, así que **queda solo en tu PC**: está en el `.gitignore`.

### Publicar

```bash
php deploy/publicar.php
```

- Sin parámetros publica en todos los sitios. Para algunos: `php deploy/publicar.php arteverde otro`.
- `--simular` muestra qué subiría sin subir nada, y `--todo` vuelve a subir todos los archivos.
- Sube solo los archivos que cambiaron desde la última vez; lo lleva en `deploy/estado/<sitio>.json`.
- **Nunca toca** el `.env`, las fotos (`web/uploads`) ni `runtime/` de los sitios.
- Incluye `vendor/`, porque en InfinityFree no hay composer, pero sin lo que no se usa en producción: barra de depuración, fuentes de Bootstrap, documentación y tests. Son unos 1.200 archivos, y la primera subida tarda varios minutos.

### Sitio nuevo

1. Creá el dominio o subdominio y la base en el hosting.
2. Agregá su bloque en `deploy/sitios.ini`.
3. Corré `php deploy/publicar.php nombre`. La primera vez sube todo y arma el `.env` del sitio: datos de la base, `COOKIE_KEY` al azar, `DEBUG = 0` y una `MANTENIMIENTO_CLAVE` al azar.
4. Actualizá la base. El script llama a `/mantenimiento/migrar` con esa clave, y en InfinityFree eso suele fallar porque el hosting frena a los programas que no son un navegador. En ese caso muestra un link `https://sitio/mantenimiento?clave=...`: abrilo y tocá **Aplicar actualizaciones**.
5. Ahí mismo aparece **Crear el primer administrador**, que reemplaza a `php yii admin/crear` en hostings sin consola. Esa página solo funciona mientras el sitio no tenga ningún usuario.

### Actualizaciones de la base de datos

Las migraciones se aplican desde la web (`components/Migraciones.php`), con la misma tabla que `php yii migrate`. Hay tres formas:
- Automáticamente, al final de `publicar.php`, si el hosting lo permite.
- Con el link de mantenimiento que muestra el script.
- Desde el panel: si hay actualizaciones pendientes, los administradores ven un aviso con el botón **Aplicar ahora**.

Si a un sitio que ya estaba subido a mano le falta la `MANTENIMIENTO_CLAVE`, el script se la agrega a su `.env` la primera vez que publica.

## Roles

| Rol | Puede |
|---|---|
| Administrador | Todo: publicaciones, categorías, usuarios, datos del emprendimiento, Acerca de y estilo del sitio |
| Editor | Solo publicaciones: crear, editar, borrar, habilitar o deshabilitar |

No hay registro público: los usuarios los crea un administrador desde el panel o con la consola.
Además, nadie puede quitarse a sí mismo el rol de administrador, desactivarse ni borrarse, y siempre queda al menos un administrador activo.

## Panel de control (`/panel`)

- **Publicaciones:** grilla con filtros y ABM. Cada publicación lleva título, descripción, categoría, precio, talle, color, material, disponibilidad y hasta 10 fotos. La primera foto es la principal y se puede cambiar. Si el precio queda vacío, se muestra "Consultar". El precio se puede escribir como `12500`, `12.500` o `12.500,50`.
- **Categorías:** ABM. Al borrar una categoría, sus publicaciones quedan sin categoría pero no se borran.
- **Usuarios:** ABM con filtros.
- **Datos del emprendimiento:** nombre, frase y foto del banner, descripción corta para compartir, datos de contacto (WhatsApp, teléfono, correo, dirección, horario, Instagram y Facebook) y qué dólar usar para los precios en dólares.
- **Acerca de:** párrafos con una o varias fotos, que se pueden reordenar. En la página, el texto va a la izquierda y las fotos a la derecha, ajustadas a la altura del texto. En el celular se apilan.
- **Estilo del sitio:** el estilo elegido vale para todos los visitantes. Hay 13 opciones: Genérico mixto, Viveros, Animales, Deportivo (rojo, azul, verde y naranja), Vehículos, Textil, Bazar, Tecnológico, Burbujas y GERM.

## Precios en dólares

Cada publicación tiene la casilla **Precio fijo en pesos**, tildada por defecto. Si se destilda, se carga un **precio de referencia en dólares** y el público ve el precio en pesos calculado con la cotización del momento, redondeado a pesos enteros. La ficha aclara, por ejemplo, "US$ 25,50 pasados a pesos al dólar blue de hoy". El orden por precio también usa el precio calculado.

- **De dónde sale la cotización:** de [dolarapi.com](https://dolarapi.com), gratis y sin clave, con el valor de venta. En Datos del emprendimiento se elige el dólar blue, oficial, MEP o tarjeta.
- **Cada cuánto se consulta:** como mucho cada 30 minutos (componente `components/Dolar.php`). Si la API no responde, se usa la última cotización guardada y no se reintenta por 5 minutos. El botón **Actualizar ahora** la consulta en el momento.
- **Cotización manual:** si se carga, se usa esa en lugar de la automática.
- **Sin cotización:** si nunca se pudo obtener ninguna, las publicaciones en dólares muestran "Consultar".

## Compartir con foto

- **Links para compartir:** los botones de compartir no usan el link de la ficha, sino uno que termina en `/foto.jpg` (`/producto/3-disco.../foto.jpg`, y `/catalogo.jpg` para el catálogo). Esa dirección devuelve solo la "tarjeta" con las etiquetas Open Graph y manda enseguida a la persona a la ficha (`SiteController::actionTarjeta`, vista `site/tarjeta.php`).
  - **Por qué:** InfinityFree frena con un JavaScript a todos los programas que no son un navegador, incluidos los robots de WhatsApp y Facebook que arman la vista previa. Solo deja pasar las direcciones que parecen imágenes (`.jpg`, `.png`, `.css`), y por eso la vista previa sale con foto. Depende de cómo filtra InfinityFree hoy: si lo cambian, el link sigue funcionando, pero sin vista previa. En otros hostings no hace falta, aunque tampoco molesta.
- **Foto como archivo:** en el celular, el botón **Con foto** del menú de compartir manda la primera foto (o el banner, para el catálogo) como archivo, con el título, el precio y el link de epígrafe. No depende de la vista previa. La foto se baja al cargar la página, porque el navegador exige que el menú se abra apenas se toca el botón.

## Fotos desde el celular

- Las fotos se comprimen en el navegador antes de subir (1600 px, o 1920 el banner, en JPG): una foto de celular de 8 MB queda en unos 300 KB. Después el servidor igual las procesa.
- En celulares y tablets aparece el botón **Sacar foto**, que abre la cámara trasera. Cada foto se suma a las elegidas de la galería.
- Si un envío supera el `post_max_size` de PHP, se avisa con un mensaje claro.

## Detalles técnicos

- **Migraciones:** `migrations/`, con `safeUp` y `safeDown`.
- **Seguridad:** las contraseñas se guardan con `password_hash` (bcrypt) y el login tiene "recordarme" de 30 días. Hay CSRF en todos los formularios, y las acciones que modifican datos solo aceptan POST (VerbFilter).
- **Fotos:** `components/Fotos.php` valida que sea una imagen real (JPG, PNG o WEBP), corrige la orientación EXIF y la guarda con GD en dos tamaños: hasta 1600 px y una miniatura de 480 px. El banner va hasta 1920 px. Las fotos quedan en `web/uploads/`, donde no se ejecuta PHP.
- **Fechas y números:** se muestran con formatos fijos (`php:d/m/Y`) y la hora de Argentina, así no hace falta `intl`.
- **Estilos:** cada estilo es un juego de variables CSS sobre `data-tema`, definido en `web/css/site.css`. La lista de estilos está en `config/params.php`.
- **Mensajes:** los avisos y las confirmaciones usan SweetAlert2.
- **Túnel local (cloudflared):** `trustedHosts` acepta 127.0.0.1, así que, detrás de un túnel, los links y las etiquetas Open Graph salen con https. Sirve para probar la vista previa de WhatsApp desde el celular.

### Estructura

```
commands/AdminController.php     php yii admin/crear
components/                      Fotos, Tema, PanelController (acceso al panel), AppAsset
config/                          web, consola, db, params, env (lee .env)
controllers/SiteController.php   portada, ficha, acerca, contacto, ingresar/salir
models/                          Usuario, Producto, Categoria, Emprendimiento, AcercaParrafo...
modules/panel/                   controladores y vistas del panel de control
views/layouts/                   main, panel, header, footer, compartir, mensajes
web/                             index.php, css, js, uploads
```

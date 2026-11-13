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
- **Datos del emprendimiento:** nombre, frase y foto del banner, descripción corta para compartir y datos de contacto (WhatsApp, teléfono, correo, dirección, horario, Instagram y Facebook).
- **Acerca de:** párrafos con una o varias fotos, que se pueden reordenar. En la página, el texto va a la izquierda y las fotos a la derecha, ajustadas a la altura del texto. En el celular se apilan.
- **Estilo del sitio:** el estilo elegido vale para todos los visitantes. Hay 11 opciones: Genérico mixto, Viveros, Animales, Deportivo (rojo, azul, verde y naranja), Vehículos, Textil, Bazar y Tecnológico.

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

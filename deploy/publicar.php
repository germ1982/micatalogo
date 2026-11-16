<?php

/**
 * Publica Mi Catálogo en los sitios de los clientes por FTP y actualiza su base de datos.
 *
 *   php deploy/publicar.php                 todos los sitios de deploy/sitios.ini
 *   php deploy/publicar.php arteverde       solo ese (se pueden poner varios)
 *   php deploy/publicar.php --simular       muestra qué subiría, sin subir nada
 *   php deploy/publicar.php --todo          vuelve a subir todos los archivos
 *
 * Primera instalación más rápida (el FTP de InfinityFree tarda con muchos archivos):
 *   php deploy/publicar.php --zip                    arma deploy/micatalogo.zip con lo mismo que subiría
 *   (subís el zip con el administrador de archivos del hosting y lo descomprimís en la carpeta del sitio)
 *   php deploy/publicar.php arteverde --ya-subido    registra que ya están, arma el .env y da el link de la base
 *
 * - Sube solo lo que cambió desde la última vez (deploy/estado/<sitio>.json).
 * - Nunca toca el .env, las fotos (web/uploads) ni runtime/ de los sitios.
 * - La primera vez arma el .env del sitio con los datos de la base de sitios.ini.
 * - Al final aplica las migraciones llamando a /mantenimiento/migrar con la clave del .env.
 *   Si el hosting no lo permite (InfinityFree frena a los programas), muestra el link
 *   para hacerlo desde el navegador.
 *
 * Para probar sin FTP: ftp_host = "local:C:/carpeta/destino" copia los archivos ahí.
 */

if (PHP_SAPI !== 'cli') {
    exit("Esto se corre desde la consola.\n");
}

const RAIZ = __DIR__ . '/..';
const ESTADO = __DIR__ . '/estado';

// Archivos del repositorio que no van a los sitios
const EXCLUIR = [
    '#^deploy/#', '#^\.git#', '#(^|/)\.gitignore$#', '#^README\.md$#', '#^\.env#',
    '#^composer\.(json|lock)$#', '#^yii$#', '#^commands/#', '#^web/uploads/(?!\.htaccess$)#',
];
// Partes de vendor/ que el sitio no usa en producción (ahorran cientos de archivos por FTP)
const EXCLUIR_VENDOR = [
    '#^vendor/yiisoft/yii2-debug/#',
    '#^vendor/twbs/bootstrap/(?!dist/)#',
    '#^vendor/bower-asset/jquery/(?!dist/)#',
    '#^vendor/bower-asset/inputmask/#',
    '#^vendor/yiisoft/yii2/messages/(?!es/)[^/]+/#',
    '#/(tests?|docs?|\.github)/#',
    '#\.(md|markdown)$#i',
];

$args = array_slice($argv, 1);
$simular = in_array('--simular', $args, true);
$todo = in_array('--todo', $args, true);
$yaSubido = in_array('--ya-subido', $args, true);
$elegidos = array_values(array_filter($args, fn($a) => !str_starts_with($a, '--')));

if (in_array('--zip', $args, true)) {
    armarZip(listarArchivos());
    exit(0);
}

$ini = getenv('SITIOS_INI') ?: __DIR__ . '/sitios.ini';   // SITIOS_INI: otro archivo (para pruebas)
if (!is_file($ini)) {
    salir("No existe deploy/sitios.ini. Copiá deploy/sitios.example.ini como sitios.ini y completalo.");
}
$sitios = parse_ini_file($ini, true, INI_SCANNER_RAW);
if ($elegidos) {
    foreach ($elegidos as $nombre) {
        isset($sitios[$nombre]) || salir("No está el sitio «{$nombre}» en sitios.ini.");
    }
    $sitios = array_intersect_key($sitios, array_flip($elegidos));
}
if (!$sitios) {
    salir('No hay sitios en sitios.ini.');
}

if (trim((string)shell_exec('git -C ' . escapeshellarg(RAIZ) . ' status --porcelain --untracked-files=no'))) {
    aviso("Ojo: hay cambios sin commitear. Se publica lo que está en la carpeta, commiteado o no.");
}
$archivos = listarArchivos();
echo count($archivos) . " archivos en el proyecto.\n";

foreach ($sitios as $nombre => $s) {
    echo "\n=== {$nombre} ===\n";
    try {
        publicar($nombre, $s, $archivos, $simular, $todo, $yaSubido);
    } catch (Throwable $e) {
        aviso("Error en {$nombre}: " . $e->getMessage());
    }
}

// ---------------------------------------------------------------------------

function publicar(string $nombre, array $s, array $archivos, bool $simular, bool $todo, bool $yaSubido): void
{
    foreach (['url', 'ftp_host', 'ftp_carpeta', 'db_host', 'db_nombre', 'db_usuario'] as $campo) {
        empty($s[$campo]) && throw new RuntimeException("falta «{$campo}» en sitios.ini");
    }
    $estadoArchivo = ESTADO . "/{$nombre}.json";
    $estado = is_file($estadoArchivo) ? json_decode(file_get_contents($estadoArchivo), true) : [];
    $subidos = $todo ? [] : ($estado['archivos'] ?? []);

    // Los archivos ya se subieron con el zip: se registran como subidos sin tocar el FTP
    if ($yaSubido) {
        guardarEstado($estadoArchivo, $archivos, $estado);
        echo count($archivos) . " archivos registrados como ya subidos (zip).\n";
        $destino = Destino::abrir($s);
        $clave = prepararEnv($destino, $s);
        $destino->cerrar();
        migrar(rtrim($s['url'], '/'), $clave);

        return;
    }

    $cambiados = [];
    foreach ($archivos as $ruta => $hash) {
        if (($subidos[$ruta] ?? null) !== $hash) {
            $cambiados[$ruta] = $hash;
        }
    }
    echo count($cambiados) . " archivos para subir.\n";
    if ($simular) {
        foreach (array_slice(array_keys($cambiados), 0, 40) as $r) {
            echo "  $r\n";
        }
        count($cambiados) > 40 && print('  … y ' . (count($cambiados) - 40) . " más\n");

        return;
    }

    $destino = Destino::abrir($s);
    $n = 0;
    foreach ($cambiados as $ruta => $hash) {
        $destino->subir(RAIZ . '/' . $ruta, $ruta);
        $subidos[$ruta] = $hash;
        if (++$n % 50 === 0) {   // se guarda de a poco: si se corta, la próxima sigue desde acá
            guardarEstado($estadoArchivo, $subidos, $estado);
            echo "  {$n}/" . count($cambiados) . "\n";
        }
    }
    guardarEstado($estadoArchivo, $subidos, $estado);
    echo "Archivos al día.\n";

    $clave = prepararEnv($destino, $s);
    $destino->cerrar();

    migrar(rtrim($s['url'], '/'), $clave);
}

/** Arma el .env la primera vez; si ya existe le agrega la clave de mantenimiento si le falta */
function prepararEnv(Destino $destino, array $s): string
{
    $actual = $destino->leer('.env');
    if ($actual === null) {
        $clave = bin2hex(random_bytes(16));
        $env = implode("\n", [
            '; Generado por deploy/publicar.php. No se sube al repositorio.',
            'DB_HOST = "' . $s['db_host'] . '"',
            'DB_NAME = "' . $s['db_nombre'] . '"',
            'DB_USERNAME = "' . $s['db_usuario'] . '"',
            'DB_PASSWORD = "' . ($s['db_clave'] ?? '') . '"',
            'COOKIE_KEY = "' . bin2hex(random_bytes(24)) . '"',
            'DEBUG = 0',
            'MANTENIMIENTO_CLAVE = "' . $clave . '"',
        ]) . "\n";
        empty($s['db_clave']) && aviso('db_clave está vacía en sitios.ini: el sitio no va a poder conectarse a la base.');
        $destino->escribir('.env', $env);
        echo "Se creó el .env del sitio.\n";

        return $clave;
    }
    if (preg_match('/^MANTENIMIENTO_CLAVE\s*=\s*"?([^"\r\n]+)"?/m', $actual, $m)) {
        return trim($m[1]);
    }
    $clave = bin2hex(random_bytes(16));
    $destino->escribir('.env', rtrim($actual) . "\nMANTENIMIENTO_CLAVE = \"{$clave}\"\n");
    echo "Se agregó la clave de mantenimiento al .env.\n";

    return $clave;
}

function migrar(string $url, string $clave): void
{
    $link = "{$url}/mantenimiento?clave={$clave}";
    $ch = curl_init("{$url}/mantenimiento/migrar");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['clave' => $clave]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_USERAGENT => 'MiCatalogo-publicar',
    ]);
    $respuesta = curl_exec($ch);
    curl_close($ch);
    $datos = is_string($respuesta) ? json_decode($respuesta, true) : null;
    if (!is_array($datos)) {
        aviso("No se pudo actualizar la base automáticamente (es normal en InfinityFree).\n"
            . "    Abrí este link en el navegador y tocá «Aplicar actualizaciones»:\n    {$link}");

        return;
    }
    if (!$datos['ok']) {
        aviso('Error al actualizar la base: ' . $datos['error']);

        return;
    }
    echo $datos['aplicadas'] ? 'Base actualizada: ' . implode(', ', $datos['aplicadas']) . "\n" : "Base de datos al día.\n";
    if ($datos['sinUsuarios']) {
        echo "El sitio no tiene administrador todavía. Crealo acá:\n    {$url}/mantenimiento/instalar?clave={$clave}\n";
    }
}

/** [ruta relativa => md5] de lo que va a los sitios */
function listarArchivos(): array
{
    $rutas = array_filter(explode("\n", (string)shell_exec('git -C ' . escapeshellarg(RAIZ) . ' ls-files --cached --others --exclude-standard')));
    $lista = [];
    foreach ($rutas as $r) {
        if (!coincide($r, EXCLUIR) && is_file(RAIZ . "/$r")) {
            $lista[$r] = md5_file(RAIZ . "/$r");
        }
    }
    // Carpetas que deben existir aunque estén vacías (sus .gitignore se excluyen arriba)
    foreach (['runtime/.gitignore', 'web/assets/.gitignore'] as $r) {
        $lista[$r] = md5_file(RAIZ . "/$r");
    }
    $base = strlen(realpath(RAIZ)) + 1;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(RAIZ . '/vendor', FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        $r = str_replace('\\', '/', substr($f->getRealPath(), $base));
        if ($f->isFile() && !coincide($r, EXCLUIR_VENDOR)) {
            $lista[$r] = md5_file($f->getRealPath());
        }
    }
    ksort($lista);

    return $lista;
}

/** deploy/micatalogo.zip con los mismos archivos que se suben por FTP (sin .env ni fotos) */
function armarZip(array $archivos): void
{
    $destino = __DIR__ . '/micatalogo.zip';
    @unlink($destino);
    $zip = new ZipArchive();
    $zip->open($destino, ZipArchive::CREATE) === true || salir('No se pudo crear el zip.');
    foreach (array_keys($archivos) as $ruta) {
        $zip->addFile(RAIZ . '/' . $ruta, $ruta);
    }
    $zip->close();
    printf("Listo: deploy/micatalogo.zip (%d archivos, %.1f MB).\n", count($archivos), filesize($destino) / 1048576);
    echo "1. Subilo con el administrador de archivos del hosting a la carpeta del sitio (htdocs).\n";
    echo "2. Descomprimilo ahí mismo y borrá el zip.\n";
    echo "3. Corré: php deploy/publicar.php <sitio> --ya-subido\n";
}

function coincide(string $ruta, array $patrones): bool
{
    foreach ($patrones as $p) {
        if (preg_match($p, $ruta)) {
            return true;
        }
    }

    return false;
}

function guardarEstado(string $archivo, array $subidos, array $estado): void
{
    is_dir(ESTADO) || mkdir(ESTADO, 0755, true);
    $estado['archivos'] = $subidos;
    $estado['ultima'] = date('Y-m-d H:i:s');
    file_put_contents($archivo, json_encode($estado, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function aviso(string $texto): void
{
    fwrite(STDERR, "  ! {$texto}\n");
}

function salir(string $texto): never
{
    fwrite(STDERR, $texto . "\n");
    exit(1);
}

/** Destino de los archivos: un servidor FTP o (para probar) una carpeta local */
abstract class Destino
{
    public static function abrir(array $s): self
    {
        return str_starts_with($s['ftp_host'], 'local:') ? new DestinoLocal($s) : new DestinoFtp($s);
    }

    abstract public function subir(string $local, string $remoto): void;
    abstract public function leer(string $remoto): ?string;
    abstract public function escribir(string $remoto, string $contenido): void;
    public function cerrar(): void {}
}

class DestinoLocal extends Destino
{
    private string $base;

    public function __construct(array $s)
    {
        $this->base = rtrim(substr($s['ftp_host'], 6), '/\\') . '/' . trim($s['ftp_carpeta'], '/\\');
    }

    public function subir(string $local, string $remoto): void
    {
        $this->escribir($remoto, file_get_contents($local));
    }

    public function leer(string $remoto): ?string
    {
        return is_file("{$this->base}/{$remoto}") ? file_get_contents("{$this->base}/{$remoto}") : null;
    }

    public function escribir(string $remoto, string $contenido): void
    {
        $destino = "{$this->base}/{$remoto}";
        is_dir(dirname($destino)) || mkdir(dirname($destino), 0755, true);
        file_put_contents($destino, $contenido);
    }
}

class DestinoFtp extends Destino
{
    private $ftp;
    private array $carpetas = [];

    public function __construct(private array $s)
    {
        $this->conectar();
    }

    private function conectar(): void
    {
        $tls = !empty($this->s['ftp_tls']) && function_exists('ftp_ssl_connect');
        $this->ftp = $tls ? @ftp_ssl_connect($this->s['ftp_host'], 21, 30) : @ftp_connect($this->s['ftp_host'], 21, 30);
        $this->ftp || throw new RuntimeException("no se pudo conectar a {$this->s['ftp_host']}");
        @ftp_login($this->ftp, $this->s['ftp_usuario'] ?? '', $this->s['ftp_clave'] ?? '')
            || throw new RuntimeException('usuario o contraseña de FTP incorrectos');
        ftp_pasv($this->ftp, true);
        $this->carpetas = [];
    }

    private function ruta(string $remoto): string
    {
        return '/' . trim($this->s['ftp_carpeta'], '/') . '/' . $remoto;
    }

    private function crearCarpetas(string $carpeta): void
    {
        $actual = '';
        foreach (explode('/', trim($carpeta, '/')) as $parte) {
            $actual .= '/' . $parte;
            if (!isset($this->carpetas[$actual])) {
                @ftp_mkdir($this->ftp, $actual);   // si ya existe, falla sin problema
                $this->carpetas[$actual] = true;
            }
        }
    }

    public function subir(string $local, string $remoto): void
    {
        $ruta = $this->ruta($remoto);
        for ($intento = 1; $intento <= 3; $intento++) {
            $this->crearCarpetas(dirname($ruta));
            if (@ftp_put($this->ftp, $ruta, $local, FTP_BINARY)) {
                return;
            }
            sleep($intento);   // InfinityFree a veces corta: se reconecta y reintenta
            @ftp_close($this->ftp);
            $this->conectar();
        }
        throw new RuntimeException("no se pudo subir {$remoto}");
    }

    public function leer(string $remoto): ?string
    {
        if (@ftp_size($this->ftp, $this->ruta($remoto)) < 0) {
            return null;
        }
        $tmp = tempnam(sys_get_temp_dir(), 'env');
        $ok = @ftp_get($this->ftp, $tmp, $this->ruta($remoto), FTP_BINARY);
        $contenido = $ok ? file_get_contents($tmp) : null;
        @unlink($tmp);

        return $contenido;
    }

    public function escribir(string $remoto, string $contenido): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'sub');
        file_put_contents($tmp, $contenido);
        try {
            $this->subir($tmp, $remoto);
        } finally {
            @unlink($tmp);
        }
    }

    public function cerrar(): void
    {
        @ftp_close($this->ftp);
    }
}

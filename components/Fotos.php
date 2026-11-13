<?php

namespace app\components;

use Yii;
use yii\web\UploadedFile;

/**
 * Fotos del sitio: se guardan en web/uploads/<carpeta>/ en dos tamaños
 * (la foto achicada y una miniatura para los listados). Se valida que sean imágenes
 * reales y se enderezan las fotos de celular según el EXIF.
 */
class Fotos
{
    private const TIPOS = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];

    /**
     * Guarda una foto subida. Devuelve [archivo, miniatura] (rutas relativas a web/)
     * o un texto con el motivo por el que no se pudo guardar.
     */
    public static function guardar(UploadedFile $subida, string $carpeta, ?int $lado = null): array|string
    {
        if ($subida->error !== UPLOAD_ERR_OK) {
            return in_array($subida->error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? "\"{$subida->name}\" es demasiado grande."
                : "No se pudo subir \"{$subida->name}\".";
        }
        $p = Yii::$app->params;
        if ($subida->size > $p['fotoMaximoMb'] * 1024 * 1024) {
            return "\"{$subida->name}\" supera los {$p['fotoMaximoMb']} MB.";
        }
        $datos = @getimagesize($subida->tempName);
        if ($datos === false || !in_array($datos[2], self::TIPOS, true)) {
            return "\"{$subida->name}\" no es una foto JPG, PNG o WEBP.";
        }

        $relativa = 'uploads/' . trim($carpeta, '/');
        $directorio = Yii::getAlias('@webroot/' . $relativa);
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true)) {
            return 'No se pudo crear la carpeta de fotos.';
        }

        $imagen = self::abrir($subida->tempName, $datos[2]);
        if ($imagen === null) {
            return "No se pudo procesar \"{$subida->name}\".";
        }

        $nombre = bin2hex(random_bytes(12));
        $grande = self::escalar($imagen, $lado ?? $p['fotoLadoMaximo']);
        $chica = self::escalar($imagen, $p['fotoLadoMiniatura']);
        $ok = imagejpeg($grande, "$directorio/$nombre.jpg", $p['fotoCalidadJpg'])
            && imagejpeg($chica, "$directorio/{$nombre}_m.jpg", 78);
        foreach ([$imagen, $grande, $chica] as $i) {
            if ($i instanceof \GdImage) {
                @imagedestroy($i);
            }
        }

        return $ok ? ["$relativa/$nombre.jpg", "$relativa/{$nombre}_m.jpg"] : 'No se pudo guardar la foto.';
    }

    /** Borra un archivo, solo si está dentro de web/uploads */
    public static function borrar(?string $ruta): void
    {
        if (!$ruta || !str_starts_with($ruta, 'uploads/')) {
            return;
        }
        $base = realpath(Yii::getAlias('@webroot/uploads'));
        $archivo = realpath(Yii::getAlias('@webroot/' . $ruta));
        if ($base && $archivo && str_starts_with($archivo, $base . DIRECTORY_SEPARATOR) && is_file($archivo)) {
            @unlink($archivo);
        }
    }

    /** Borra la carpeta de un producto o párrafo con todas sus fotos */
    public static function borrarCarpeta(string $carpeta): void
    {
        $directorio = Yii::getAlias('@webroot/uploads/' . trim($carpeta, '/'));
        if (is_dir($directorio)) {
            array_map('unlink', glob("$directorio/*") ?: []);
            @rmdir($directorio);
        }
    }

    /** Dirección completa (con https://dominio), para las etiquetas Open Graph */
    public static function urlAbsoluta(?string $ruta): string
    {
        return Yii::$app->request->hostInfo . self::url($ruta);
    }

    public static function url(?string $ruta): string
    {
        return Yii::$app->request->baseUrl . '/' . ltrim((string)$ruta, '/');
    }

    private static function abrir(string $origen, int $tipo): ?\GdImage
    {
        $imagen = match ($tipo) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($origen),
            IMAGETYPE_PNG => @imagecreatefrompng($origen),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($origen) : false,
            default => false,
        };
        if (!$imagen) {
            return null;
        }

        // Fondo blanco para PNG/WEBP con transparencia (se guardan como JPG)
        if ($tipo !== IMAGETYPE_JPEG) {
            $fondo = imagecreatetruecolor(imagesx($imagen), imagesy($imagen));
            imagefill($fondo, 0, 0, imagecolorallocate($fondo, 255, 255, 255));
            imagecopy($fondo, $imagen, 0, 0, 0, 0, imagesx($imagen), imagesy($imagen));
            imagedestroy($imagen);
            $imagen = $fondo;
        }

        if ($tipo === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($origen);
            $giro = ['3' => 180, '6' => -90, '8' => 90][(string)($exif['Orientation'] ?? '')] ?? 0;
            if ($giro && ($girada = imagerotate($imagen, $giro, 0))) {
                imagedestroy($imagen);
                $imagen = $girada;
            }
        }

        return $imagen;
    }

    private static function escalar(\GdImage $imagen, int $lado): \GdImage
    {
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        $escala = min(1, $lado / max($ancho, $alto));
        $nuevoAncho = max(1, (int)round($ancho * $escala));
        $nuevoAlto = max(1, (int)round($alto * $escala));
        $copia = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagecopyresampled($copia, $imagen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

        return $copia;
    }
}

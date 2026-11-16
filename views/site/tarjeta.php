<?php

/**
 * Tarjeta para compartir: lo que leen WhatsApp, Facebook y compañía para armar la vista
 * previa. Las personas que abren el link pasan directo a $destino.
 *
 * @var yii\web\View $this
 * @var app\models\Emprendimiento $sitio
 * @var string $titulo
 * @var string $descripcion
 * @var string|null $imagen   ruta dentro de web/
 * @var string $destino       dirección absoluta de la ficha o del catálogo
 * @var string $tipo          website | product
 */

use app\components\Fotos;
use yii\helpers\Html;
use yii\helpers\Url;

$medidas = $imagen ? @getimagesize(Yii::getAlias('@webroot/' . $imagen)) : false;
$etiquetas = [
    'og:site_name' => $sitio->nombre,
    'og:locale' => 'es_AR',
    'og:type' => $tipo,
    'og:title' => $titulo,
    'og:description' => $descripcion,
    // Esta misma dirección: si fuera la ficha, el robot la volvería a pedir y lo frenarían
    'og:url' => Url::to(Yii::$app->request->url, true),
];
if ($imagen) {
    $etiquetas['og:image'] = Fotos::urlAbsoluta($imagen);
    if ($medidas) {
        $etiquetas['og:image:width'] = $medidas[0];
        $etiquetas['og:image:height'] = $medidas[1];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= Html::encode($titulo) ?></title>
    <meta name="robots" content="noindex">
    <meta name="description" content="<?= Html::encode($descripcion) ?>">
    <?php foreach ($etiquetas as $propiedad => $valor): ?>
    <meta property="<?= $propiedad ?>" content="<?= Html::encode($valor) ?>">
    <?php endforeach; ?>
    <meta name="twitter:card" content="<?= $imagen ? 'summary_large_image' : 'summary' ?>">
    <link rel="canonical" href="<?= Html::encode($destino) ?>">
    <meta http-equiv="refresh" content="0; url=<?= Html::encode($destino) ?>">
    <script>location.replace(<?= json_encode($destino, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>);</script>
</head>
<body>
    <p><a href="<?= Html::encode($destino) ?>"><?= Html::encode($titulo) ?></a></p>
</body>
</html>

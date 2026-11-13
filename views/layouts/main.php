<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\components\AppAsset;
use app\components\Fotos;
use app\components\Tema;
use app\models\Emprendimiento;
use yii\bootstrap5\Html;
use yii\helpers\Url;

AppAsset::register($this);

$sitio = Emprendimiento::actual();
$tema = Tema::actual();
$datosTema = Tema::datos($tema);
$descripcion = $this->params['descripcion'] ?? ($sitio->descripcion ?: ($sitio->eslogan ?: 'Catálogo online de ' . $sitio->nombre));

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']);
$this->registerMetaTag(['name' => 'description', 'content' => $descripcion]);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/svg+xml', 'href' => Yii::getAlias('@web/favicon.svg')]);
$this->registerLinkTag(['rel' => 'preconnect', 'href' => 'https://fonts.gstatic.com', 'crossorigin' => true]);
$this->registerCssFile(Tema::urlFuentes(array_merge([$tema], $this->params['temasFuentes'] ?? [])), ['position' => $this::POS_HEAD]);

// Vista previa al compartir el link (WhatsApp, Facebook...): etiquetas Open Graph.
// Cada vista puede completar $this->params['og'] = [title, description, image, url, type].
$og = $this->params['og'] ?? [];
$ogDatos = [
    'og:site_name' => $sitio->nombre,
    'og:locale' => 'es_AR',
    'og:type' => $og['type'] ?? 'website',
    'og:title' => $og['title'] ?? ($this->title ? $this->title . ' · ' . $sitio->nombre : $sitio->nombre),
    'og:description' => $og['description'] ?? $descripcion,
    'og:url' => $og['url'] ?? Url::to(Yii::$app->request->url, true),
];
$imagen = $og['image'] ?? ($sitio->banner ? Fotos::urlAbsoluta($sitio->banner) : null);
if ($imagen) {
    $ogDatos['og:image'] = $imagen;
}
foreach ($ogDatos as $propiedad => $valor) {
    $this->registerMetaTag(['property' => $propiedad, 'content' => $valor], $propiedad);
}
$this->registerMetaTag(['name' => 'twitter:card', 'content' => $imagen ? 'summary_large_image' : 'summary'], 'twitter:card');
if (isset($og['url'])) {
    $this->registerLinkTag(['rel' => 'canonical', 'href' => $og['url']], 'canonical');
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="es" class="h-100" data-tema="<?= $tema ?>" data-bs-theme="<?= $datosTema['oscuro'] ? 'dark' : 'light' ?>"
      style="--fuente-titulos: '<?= Html::encode($datosTema['fuente']) ?>'">
<head>
    <title><?= Html::encode($this->title ? $this->title . ' · ' . $sitio->nombre : $sitio->nombre) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<?= $this->render('_header', ['sitio' => $sitio, 'icono' => $datosTema['icono']]) ?>

<main id="main" class="flex-shrink-0" role="main">
    <?= $content ?>
</main>

<?= $this->render('_footer', ['sitio' => $sitio]) ?>

<?php $this->endBody() ?>
<?= $this->render('_mensajes') ?>
</body>
</html>
<?php $this->endPage() ?>

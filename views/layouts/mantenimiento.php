<?php

/**
 * Diseño mínimo para mantenimiento e instalación: no lee nada de la base
 * (puede que las tablas todavía no existan).
 *
 * @var yii\web\View $this
 * @var string $content
 */

use yii\helpers\Html;

\yii\bootstrap5\BootstrapAsset::register($this);
$this->registerCsrfMetaTags();
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= Html::encode($this->title ?: 'Mantenimiento') ?></title>
    <?php $this->head() ?>
    <style>
        body { background: #f4f5f8; }
        .caja-mantenimiento { max-width: 34rem; margin: 3rem auto; background: #fff; border-radius: 14px; box-shadow: 0 6px 24px rgba(0, 0, 0, .08); padding: 1.75rem; }
    </style>
</head>
<body>
<?php $this->beginBody() ?>
<div class="container">
    <div class="caja-mantenimiento"><?= $content ?></div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

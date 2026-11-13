<?php

/** @var yii\web\View $this */
/** @var app\models\Emprendimiento $sitio */
/** @var string $icono */

use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

$ruta = Yii::$app->controller->route;
$logueado = !Yii::$app->user->isGuest;
?>
<header id="header">
    <?php
    NavBar::begin([
        'brandLabel' => '<i class="bi ' . $icono . '"></i> ' . Html::encode($sitio->nombre),
        'brandUrl' => Yii::$app->homeUrl,
        'brandOptions' => ['class' => 'marca'],
        'options' => ['class' => 'navbar-expand-md fixed-top barra', 'data-bs-theme' => 'dark'],
        'collapseOptions' => ['class' => 'justify-content-end'],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav align-items-md-center gap-md-1'],
        'encodeLabels' => false,
        'items' => [
            ['label' => 'Inicio', 'url' => ['/site/index'], 'active' => in_array($ruta, ['site/index', 'site/producto'], true)],
            ['label' => 'Acerca de', 'url' => ['/site/acerca']],
            ['label' => 'Contacto', 'url' => ['/site/contacto']],
            [
                'label' => '<i class="bi bi-speedometer2"></i> Panel',
                'url' => ['/panel/publicacion/index'],
                'visible' => $logueado,
                'active' => str_starts_with($ruta, 'panel/'),
                'linkOptions' => ['class' => 'btn-panel'],
            ],
        ],
    ]);
    NavBar::end();
    ?>
</header>

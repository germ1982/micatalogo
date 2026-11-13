<?php

/**
 * @var yii\web\View $this
 * @var string $name
 * @var string $message
 * @var Exception $exception
 */

use yii\helpers\Html;

$this->title = $name;
$codigo = $exception instanceof \yii\web\HttpException ? $exception->statusCode : 500;
?>
<div class="container contenido">
    <div class="vacio">
        <i class="bi <?= $codigo === 404 ? 'bi-signpost-split' : 'bi-exclamation-triangle' ?>"></i>
        <h1 class="h3"><?= $codigo === 404 ? 'No encontramos esa página' : ($codigo === 403 ? 'No tenés permiso para entrar acá' : 'Algo salió mal') ?></h1>
        <p><?= nl2br(Html::encode($message)) ?></p>
        <?= Html::a('Volver al catálogo', ['/site/index'], ['class' => 'btn btn-primario']) ?>
    </div>
</div>

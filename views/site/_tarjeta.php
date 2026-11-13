<?php

/**
 * Tarjeta de un producto en la grilla. Si hay alguien logueado muestra
 * si está disponible y los botones para editar y habilitar/deshabilitar.
 *
 * @var yii\web\View $this
 * @var app\models\Producto $producto
 * @var bool $logueado
 */

use app\components\Fotos;
use yii\helpers\Html;

$foto = $producto->fotoPrincipal;
?>
<article class="tarjeta <?= $producto->disponible ? '' : 'no-disponible' ?>">
    <div class="tarjeta-foto">
        <?php if ($foto): ?>
            <img src="<?= Html::encode(Fotos::url($foto->miniatura)) ?>" alt="<?= Html::encode($producto->titulo) ?>" loading="lazy">
        <?php else: ?>
            <span class="sin-foto"><i class="bi bi-image"></i></span>
        <?php endif; ?>
        <?php if ($logueado): ?>
            <span class="estado <?= $producto->disponible ? 'si' : 'no' ?>">
                <i class="bi <?= $producto->disponible ? 'bi-eye' : 'bi-eye-slash' ?>"></i>
                <?= $producto->disponible ? 'Disponible' : 'No disponible' ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="tarjeta-cuerpo">
        <?php if ($producto->categoria): ?>
            <small class="tarjeta-categoria"><?= Html::encode($producto->categoria->nombre) ?></small>
        <?php endif; ?>
        <h2 class="tarjeta-titulo">
            <?= Html::a(Html::encode($producto->titulo), $producto->url, ['class' => 'stretched-link']) ?>
        </h2>
        <div class="tarjeta-precio"><?= $producto->precioTexto ?></div>
    </div>
    <?php if ($logueado): ?>
        <div class="tarjeta-admin">
            <?= Html::a('<i class="bi bi-pencil"></i> Editar', ['/panel/publicacion/editar', 'id' => $producto->id, 'volver' => Yii::$app->request->url],
                ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?= Html::beginForm(['/panel/publicacion/disponible', 'id' => $producto->id], 'post') ?>
            <?= Html::hiddenInput('volver', Yii::$app->request->url) ?>
            <?= Html::submitButton($producto->disponible ? '<i class="bi bi-eye-slash"></i> Deshabilitar' : '<i class="bi bi-eye"></i> Habilitar', [
                'class' => 'btn btn-sm ' . ($producto->disponible ? 'btn-outline-danger' : 'btn-outline-success'),
                'title' => $producto->disponible ? 'Ocultar al público' : 'Mostrar al público',
            ]) ?>
            <?= Html::endForm() ?>
        </div>
    <?php endif; ?>
</article>

<?php

/**
 * Tarjeta de un producto en la grilla. Todos ven el botón para compartir por WhatsApp;
 * si hay alguien logueado muestra además si está disponible y los botones (solo ícono)
 * para editar y habilitar/deshabilitar.
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
        <div class="tarjeta-pie">
            <div class="tarjeta-precio"><?= $producto->precioTexto ?></div>
            <div class="tarjeta-acciones">
                <?= Html::a('<i class="bi bi-whatsapp"></i>',
                    'https://wa.me/?text=' . rawurlencode($producto->titulo . ' · ' . strip_tags($producto->precioTexto) . "\n" . $producto->getUrlCompartir()), [
                        'class' => 'btn-icono whatsapp', 'target' => '_blank', 'rel' => 'noopener',
                        'title' => 'Compartir por WhatsApp', 'aria-label' => 'Compartir por WhatsApp',
                    ]) ?>
                <?php if ($logueado): ?>
                    <?= Html::a('<i class="bi bi-pencil"></i>', ['/panel/publicacion/editar', 'id' => $producto->id, 'volver' => Yii::$app->request->url],
                        ['class' => 'btn-icono', 'title' => 'Editar', 'aria-label' => 'Editar']) ?>
                    <?= Html::beginForm(['/panel/publicacion/disponible', 'id' => $producto->id], 'post') ?>
                    <?= Html::hiddenInput('volver', Yii::$app->request->url) ?>
                    <?php $accion = $producto->disponible ? 'Deshabilitar (ocultar al público)' : 'Habilitar (mostrar al público)' ?>
                    <?= Html::submitButton('<i class="bi ' . ($producto->disponible ? 'bi-eye-slash' : 'bi-eye') . '"></i>', [
                        'class' => 'btn-icono ' . ($producto->disponible ? 'peligro' : 'exito'),
                        'title' => $accion, 'aria-label' => $accion,
                    ]) ?>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>

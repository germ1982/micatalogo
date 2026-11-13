<?php

/**
 * @var yii\web\View $this
 * @var app\models\AcercaParrafo[] $parrafos
 */

use app\components\Fotos;
use yii\helpers\Html;
use yii\helpers\StringHelper;

$this->title = 'Acerca de';
$ultimo = count($parrafos) - 1;
?>
<div class="encabezado-panel">
    <h1><i class="bi bi-card-text"></i> Acerca de</h1>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="bi bi-eye"></i> Ver página', ['/site/acerca'], ['class' => 'btn btn-outline-secondary', 'target' => '_blank']) ?>
        <?= Html::a('<i class="bi bi-plus-lg"></i> Nuevo párrafo', ['crear'], ['class' => 'btn btn-primario']) ?>
    </div>
</div>
<p class="text-body-secondary">Cada párrafo se muestra con el texto a la izquierda y sus fotos a la derecha.</p>

<?php if (!$parrafos): ?>
    <div class="caja text-body-secondary">Todavía no hay párrafos. Agregá el primero contando quiénes son y qué hacen.</div>
<?php endif; ?>

<?php foreach ($parrafos as $i => $parrafo): ?>
    <div class="caja parrafo-panel">
        <div class="parrafo-orden">
            <?= $i > 0 ? Html::a('<i class="bi bi-arrow-up"></i>', ['mover', 'id' => $parrafo->id, 'hacia' => 'arriba'],
                ['class' => 'btn btn-sm btn-outline-secondary', 'data-method' => 'post', 'title' => 'Subir']) : '' ?>
            <?= $i < $ultimo ? Html::a('<i class="bi bi-arrow-down"></i>', ['mover', 'id' => $parrafo->id, 'hacia' => 'abajo'],
                ['class' => 'btn btn-sm btn-outline-secondary', 'data-method' => 'post', 'title' => 'Bajar']) : '' ?>
        </div>
        <div class="flex-grow-1">
            <?php if ($parrafo->titulo): ?><strong><?= Html::encode($parrafo->titulo) ?></strong><br><?php endif; ?>
            <span class="text-body-secondary"><?= Html::encode(StringHelper::truncate($parrafo->texto, 160)) ?></span>
            <?php if ($parrafo->fotos): ?>
                <div class="d-flex gap-1 mt-2">
                    <?php foreach ($parrafo->fotos as $foto): ?>
                        <img src="<?= Html::encode(Fotos::url($foto->miniatura)) ?>" alt="" class="miniatura-chica">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-nowrap">
            <?= Html::a('<i class="bi bi-pencil"></i>', ['editar', 'id' => $parrafo->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Editar']) ?>
            <?= Html::a('<i class="bi bi-trash"></i>', ['borrar', 'id' => $parrafo->id], [
                'class' => 'btn btn-sm btn-outline-danger', 'title' => 'Borrar', 'data-method' => 'post',
                'data-confirm' => '¿Borrar este párrafo con sus fotos?',
            ]) ?>
        </div>
    </div>
<?php endforeach; ?>

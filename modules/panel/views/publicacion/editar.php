<?php

/**
 * @var yii\web\View $this
 * @var app\models\Producto $producto
 */

use yii\helpers\Html;

$this->title = 'Editar publicación';
?>
<div class="encabezado-panel">
    <h1><i class="bi bi-pencil"></i> Editar publicación</h1>
    <?= Html::a('<i class="bi bi-box-arrow-up-right"></i> Ver en el catálogo', $producto->url, ['class' => 'btn btn-outline-secondary', 'target' => '_blank']) ?>
</div>
<?= $this->render('_formulario', ['producto' => $producto]) ?>

<?php

/**
 * @var yii\web\View $this
 * @var app\models\Producto $producto
 */

$this->title = 'Nueva publicación';
?>
<div class="encabezado-panel">
    <h1><i class="bi bi-plus-lg"></i> Nueva publicación</h1>
</div>
<?= $this->render('_formulario', ['producto' => $producto]) ?>

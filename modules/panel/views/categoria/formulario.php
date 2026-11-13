<?php

/**
 * @var yii\web\View $this
 * @var app\models\Categoria $categoria
 */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = $categoria->isNewRecord ? 'Nueva categoría' : 'Editar categoría';
?>
<div class="encabezado-panel">
    <h1><i class="bi bi-tags"></i> <?= $this->title ?></h1>
</div>
<?php $form = ActiveForm::begin(['options' => ['class' => 'caja', 'style' => 'max-width: 32rem']]); ?>
<?= $form->field($categoria, 'nombre')->textInput(['maxlength' => 60, 'autofocus' => true, 'placeholder' => 'Ej.: Ropa, Plantas, Bazar']) ?>
<div class="d-flex gap-2">
    <?= Html::submitButton('<i class="bi bi-check-lg"></i> Guardar', ['class' => 'btn btn-primario']) ?>
    <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-link']) ?>
</div>
<?php ActiveForm::end(); ?>

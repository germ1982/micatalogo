<?php

/**
 * @var yii\web\View $this
 * @var app\models\Usuario $usuario
 */

use app\models\Usuario;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = $usuario->isNewRecord ? 'Nuevo usuario' : 'Editar usuario';
?>
<div class="encabezado-panel">
    <h1><i class="bi bi-person"></i> <?= $this->title ?></h1>
</div>
<?php $form = ActiveForm::begin(['options' => ['class' => 'caja', 'style' => 'max-width: 40rem', 'autocomplete' => 'off']]); ?>
<div class="row">
    <div class="col-md-6"><?= $form->field($usuario, 'usuario')->textInput(['maxlength' => 40, 'autocomplete' => 'off']) ?></div>
    <div class="col-md-6"><?= $form->field($usuario, 'nombre')->textInput(['maxlength' => 80]) ?></div>
</div>
<?= $form->field($usuario, 'rol')->radioList(Usuario::ROLES) ?>
<?= $form->field($usuario, 'clave')->passwordInput(['autocomplete' => 'new-password'])->hint('Mínimo 8 caracteres.') ?>
<?= $form->field($usuario, 'activo')->checkbox()->hint('Un usuario inactivo no puede ingresar.') ?>
<div class="d-flex gap-2">
    <?= Html::submitButton('<i class="bi bi-check-lg"></i> Guardar', ['class' => 'btn btn-primario']) ?>
    <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-link']) ?>
</div>
<?php ActiveForm::end(); ?>

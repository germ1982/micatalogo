<?php

/**
 * Crea el primer administrador de un sitio nuevo (en hostings sin consola).
 *
 * @var yii\web\View $this
 * @var app\models\Usuario $usuario
 * @var string $clave
 */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Instalación';
?>
<h1 class="h4">Primer administrador</h1>
<p class="text-body-secondary small">El sitio todavía no tiene usuarios. Creá el administrador con el que vas a entrar al panel.</p>

<?php $form = ActiveForm::begin(['action' => ['instalar', 'clave' => $clave], 'options' => ['autocomplete' => 'off']]); ?>
<?= $form->field($usuario, 'usuario')->textInput(['autofocus' => true, 'autocomplete' => 'username'])->label('Usuario (un nombre corto o tu email)') ?>
<?= $form->field($usuario, 'nombre')->label('Nombre (el que se ve en el panel)') ?>
<?= $form->field($usuario, 'clave')->passwordInput(['autocomplete' => 'new-password'])->label('Contraseña')->hint('Mínimo 8 caracteres.') ?>
<div class="mb-3">
    <label class="form-label" for="repetir">Repetí la contraseña</label>
    <?= Html::passwordInput('repetir', '', ['id' => 'repetir', 'class' => 'form-control', 'autocomplete' => 'new-password', 'required' => true]) ?>
</div>
<?= Html::submitButton('Crear administrador', ['class' => 'btn btn-primary w-100']) ?>
<?php ActiveForm::end(); ?>

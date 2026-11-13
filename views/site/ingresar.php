<?php

/**
 * @var yii\web\View $this
 * @var app\models\LoginForm $modelo
 */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Ingresar';
$this->registerMetaTag(['name' => 'robots', 'content' => 'noindex'], 'robots');
?>
<div class="container contenido">
    <div class="row justify-content-center">
        <div class="col-sm-9 col-md-6 col-lg-4">
            <div class="caja">
                <h1 class="h4 mb-3"><i class="bi bi-person-lock"></i> Ingresar al panel</h1>
                <?php $form = ActiveForm::begin(['id' => 'form-ingresar']); ?>
                <?= $form->field($modelo, 'usuario')->textInput(['autofocus' => true, 'autocomplete' => 'username']) ?>
                <?= $form->field($modelo, 'clave')->passwordInput(['autocomplete' => 'current-password']) ?>
                <?= $form->field($modelo, 'recordarme')->checkbox() ?>
                <?= Html::submitButton('Ingresar', ['class' => 'btn btn-primario w-100']) ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

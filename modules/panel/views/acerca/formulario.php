<?php

/**
 * @var yii\web\View $this
 * @var app\models\AcercaParrafo $parrafo
 */

use app\components\Fotos;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = $parrafo->isNewRecord ? 'Nuevo párrafo' : 'Editar párrafo';
?>
<div class="encabezado-panel">
    <h1><i class="bi bi-card-text"></i> <?= $this->title ?></h1>
</div>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'class' => 'caja']]); ?>
<?= $form->field($parrafo, 'titulo')->textInput(['maxlength' => 120]) ?>
<?= $form->field($parrafo, 'texto')->textarea(['rows' => 7]) ?>

<?php if (!$parrafo->isNewRecord && $parrafo->fotos): ?>
    <label class="form-label">Fotos cargadas</label>
    <div class="fotos-cargadas mb-3">
        <?php foreach ($parrafo->fotos as $foto): ?>
            <div class="foto-cargada">
                <img src="<?= Html::encode(Fotos::url($foto->miniatura)) ?>" alt="">
                <div class="foto-botones">
                    <?= Html::a('<i class="bi bi-trash"></i>', ['borrar-foto', 'id' => $foto->id], [
                        'class' => 'btn btn-sm btn-danger', 'title' => 'Borrar foto',
                        'data-method' => 'post', 'data-form' => 'form-fotos', 'data-confirm' => '¿Borrar esta foto?',
                    ]) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $form->field($parrafo, 'fotosNuevas[]')->fileInput([
    'multiple' => true, 'accept' => 'image/jpeg,image/png,image/webp', 'class' => 'form-control', 'data-vista-previa' => '#vista-previa',
    'data-comprimir' => Yii::$app->params['fotoLadoMaximo'],
])->label('Agregar fotos')->hint('Una o varias (hasta 6 por vez). Se ajustan a la altura del texto.') ?>
<?= $this->render('@app/views/layouts/_camara', ['destino' => '#acercaparrafo-fotosnuevas']) ?>
<div id="vista-previa" class="fotos-cargadas mb-3"></div>

<div class="d-flex gap-2">
    <?= Html::submitButton('<i class="bi bi-check-lg"></i> Guardar', ['class' => 'btn btn-primario']) ?>
    <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-link']) ?>
</div>
<?php ActiveForm::end(); ?>

<?= Html::beginForm('', 'post', ['id' => 'form-fotos', 'class' => 'd-none']) . Html::endForm() ?>

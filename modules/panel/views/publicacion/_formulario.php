<?php

/**
 * Formulario de publicación. Las fotos ya cargadas tienen sus propios botones
 * (borrar / hacer principal), que se envían por POST con el formulario #form-fotos.
 *
 * @var yii\web\View $this
 * @var app\models\Producto $producto
 */

use app\components\Fotos;
use app\models\Categoria;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$volver = (string)Yii::$app->request->get('volver', Yii::$app->request->post('volver', ''));
$maximo = Yii::$app->params['fotosPorProducto'];
?>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'class' => 'caja']]); ?>
<?= Html::hiddenInput('volver', $volver) ?>

<div class="row">
    <div class="col-md-8"><?= $form->field($producto, 'titulo')->textInput(['maxlength' => 120, 'autofocus' => $producto->isNewRecord]) ?></div>
    <div class="col-md-4">
        <?= $form->field($producto, 'precio')->textInput(['inputmode' => 'decimal', 'placeholder' => 'Vacío = "Consultar"'])
            ->hint('Ej.: 12500 o 12.500,50') ?>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <?= $form->field($producto, 'categoria_id')->dropDownList(Categoria::lista(), ['prompt' => '(sin categoría)'])
            ->hint(Yii::$app->user->identity->esAdmin() ? Html::a('Administrar categorías', ['/panel/categoria/index']) : '') ?>
    </div>
    <div class="col-md-6 d-flex align-items-center">
        <?= $form->field($producto, 'disponible', ['options' => ['class' => 'form-check form-switch mt-md-3']])
            ->checkbox(['class' => 'form-check-input', 'role' => 'switch'], false)
            ->label('Disponible (el público la ve)', ['class' => 'form-check-label']) ?>
    </div>
</div>
<div class="row">
    <div class="col-md-4"><?= $form->field($producto, 'talle')->textInput(['maxlength' => 60, 'placeholder' => 'Ej.: S, M, L o 38 al 42']) ?></div>
    <div class="col-md-4"><?= $form->field($producto, 'color')->textInput(['maxlength' => 60]) ?></div>
    <div class="col-md-4"><?= $form->field($producto, 'material')->textInput(['maxlength' => 80]) ?></div>
</div>
<?= $form->field($producto, 'descripcion')->textarea(['rows' => 6]) ?>

<?php if (!$producto->isNewRecord && $producto->fotos): ?>
    <label class="form-label">Fotos cargadas <small class="text-body-secondary">(la primera es la principal)</small></label>
    <div class="fotos-cargadas mb-3">
        <?php foreach ($producto->fotos as $i => $foto): ?>
            <div class="foto-cargada">
                <img src="<?= Html::encode(Fotos::url($foto->miniatura)) ?>" alt="">
                <div class="foto-botones">
                    <?php if ($i > 0): ?>
                        <?= Html::a('<i class="bi bi-star"></i>', ['principal-foto', 'id' => $foto->id], [
                            'class' => 'btn btn-sm btn-light', 'title' => 'Hacer principal',
                            'data-method' => 'post', 'data-form' => 'form-fotos',
                        ]) ?>
                    <?php else: ?>
                        <span class="badge text-bg-warning"><i class="bi bi-star-fill"></i> Principal</span>
                    <?php endif; ?>
                    <?= Html::a('<i class="bi bi-trash"></i>', ['borrar-foto', 'id' => $foto->id], [
                        'class' => 'btn btn-sm btn-danger', 'title' => 'Borrar foto',
                        'data-method' => 'post', 'data-form' => 'form-fotos', 'data-confirm' => '¿Borrar esta foto?',
                    ]) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $form->field($producto, 'fotosNuevas[]')->fileInput([
    'multiple' => true, 'accept' => 'image/jpeg,image/png,image/webp', 'class' => 'form-control', 'data-vista-previa' => '#vista-previa',
])->label('Agregar fotos')->hint("JPG, PNG o WEBP, hasta $maximo por publicación. Se achican solas.") ?>
<div id="vista-previa" class="fotos-cargadas mb-3"></div>

<div class="d-flex flex-wrap gap-2">
    <?= Html::submitButton('<i class="bi bi-check-lg"></i> Guardar', ['class' => 'btn btn-primario']) ?>
    <?= Html::submitButton('Guardar y seguir editando', ['class' => 'btn btn-outline-secondary', 'name' => 'seguir', 'value' => 1]) ?>
    <?= Html::a('Cancelar', $volver !== '' && str_starts_with($volver, '/') && !str_starts_with($volver, '//') ? $volver : ['index'],
        ['class' => 'btn btn-link']) ?>
</div>
<?php ActiveForm::end(); ?>

<?php // Formulario aparte para los botones de las fotos (yii.js lo envía a la acción de cada botón) ?>
<?= Html::beginForm('', 'post', ['id' => 'form-fotos', 'class' => 'd-none']) ?>
<?= Html::hiddenInput('volver', $volver) ?>
<?= Html::endForm() ?>

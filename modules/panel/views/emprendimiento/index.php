<?php

/**
 * @var yii\web\View $this
 * @var app\models\Emprendimiento $datos
 */

use app\components\Dolar;
use app\components\Fotos;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Datos del emprendimiento';
?>
<div class="encabezado-panel">
    <h1><i class="bi bi-shop"></i> Datos del emprendimiento</h1>
</div>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'class' => 'caja']]); ?>
<h2 class="h6 subtitulo-panel">Portada</h2>
<div class="row">
    <div class="col-md-6"><?= $form->field($datos, 'nombre')->textInput(['maxlength' => 80]) ?></div>
    <div class="col-md-6"><?= $form->field($datos, 'eslogan')->textInput(['maxlength' => 160]) ?></div>
</div>
<?= $form->field($datos, 'descripcion')->textarea(['rows' => 2, 'maxlength' => 300]) ?>

<div class="row align-items-end">
    <div class="col-md-6">
        <?= $form->field($datos, 'bannerNuevo')->fileInput(['accept' => 'image/jpeg,image/png,image/webp', 'class' => 'form-control', 'data-comprimir' => Yii::$app->params['fotoLadoBanner']])
            ->hint('Una foto horizontal de tu emprendimiento. Se achica sola a 1920 px.') ?>
    </div>
    <div class="col-md-6 mb-3">
        <?php if ($datos->banner): ?>
            <div class="banner-actual">
                <img src="<?= Html::encode(Fotos::url($datos->banner)) ?>" alt="Banner actual">
                <?= Html::a('<i class="bi bi-trash"></i> Quitar', ['quitar-banner'], [
                    'class' => 'btn btn-sm btn-danger', 'data-method' => 'post', 'data-form' => 'form-aparte',
                    'data-confirm' => '¿Quitar la imagen del banner?',
                ]) ?>
            </div>
        <?php else: ?>
            <small class="text-body-secondary">Sin imagen: el banner usa los colores del estilo.</small>
        <?php endif; ?>
    </div>
</div>

<h2 class="h6 subtitulo-panel mt-3">Contacto</h2>
<div class="row">
    <div class="col-md-6"><?= $form->field($datos, 'whatsapp')->textInput(['inputmode' => 'tel', 'placeholder' => '5492991234567']) ?></div>
    <div class="col-md-6"><?= $form->field($datos, 'telefono')->textInput() ?></div>
    <div class="col-md-6"><?= $form->field($datos, 'email')->textInput(['type' => 'email']) ?></div>
    <div class="col-md-6"><?= $form->field($datos, 'direccion')->textInput() ?></div>
    <div class="col-md-6"><?= $form->field($datos, 'horario')->textInput(['placeholder' => 'Lunes a viernes de 9 a 18']) ?></div>
    <div class="col-md-6"><?= $form->field($datos, 'instagram')->textInput(['placeholder' => 'miemprendimiento']) ?></div>
    <div class="col-md-12"><?= $form->field($datos, 'facebook')->textInput(['placeholder' => 'https://www.facebook.com/miemprendimiento']) ?></div>
</div>

<h2 class="h6 subtitulo-panel mt-3" id="dolar">Precios en dólares</h2>
<p class="small text-body-secondary">
    Las publicaciones sin "precio fijo en pesos" muestran su precio en dólares pasado a pesos con esta cotización.
    Se consulta sola cada 30 minutos en dolarapi.com.
</p>
<div class="row align-items-start">
    <div class="col-md-5"><?= $form->field($datos, 'dolar_tipo')->dropDownList(Dolar::TIPOS) ?></div>
    <div class="col-md-4">
        <?= $form->field($datos, 'dolar_manual')->textInput(['inputmode' => 'decimal', 'placeholder' => 'Vacío = automática'])
            ->hint('Si la cargás, se usa esta en lugar de la automática.') ?>
    </div>
    <div class="col-md-3">
        <label class="form-label">Cotización en uso</label>
        <div class="cotizacion-actual">
            <?php if (Dolar::cotizacion() !== null): ?>
                <strong>$ <?= number_format(Dolar::cotizacion(), 2, ',', '.') ?></strong>
                <small class="d-block text-body-secondary">
                    <?= $datos->dolar_manual !== null ? 'Manual' : Html::encode(Dolar::TIPOS[$datos->dolar_tipo] ?? '')
                        . ($datos->dolar_actualizado ? ' · ' . Yii::$app->formatter->asDatetime($datos->dolar_actualizado) : '') ?>
                </small>
            <?php else: ?>
                <small class="text-body-secondary">Todavía sin cotización</small>
            <?php endif; ?>
            <?= Html::a('<i class="bi bi-arrow-clockwise"></i> Actualizar ahora', ['actualizar-dolar'], [
                'class' => 'btn btn-sm btn-outline-secondary mt-1', 'data-method' => 'post', 'data-form' => 'form-aparte',
            ]) ?>
        </div>
    </div>
</div>

<?= Html::submitButton('<i class="bi bi-check-lg"></i> Guardar', ['class' => 'btn btn-primario']) ?>
<?php ActiveForm::end(); ?>

<?= Html::beginForm('', 'post', ['id' => 'form-aparte', 'class' => 'd-none']) . Html::endForm() ?>

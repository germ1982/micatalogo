<?php

/**
 * Botón "Sacar foto": abre la cámara trasera del celular (capture="environment").
 * Cada foto sacada se suma al campo de fotos indicado en $destino, así se pueden
 * sacar varias seguidas y mezclarlas con las elegidas de la galería.
 * Solo se muestra en pantallas táctiles (en la PC no tiene sentido).
 *
 * @var yii\web\View $this
 * @var string $destino selector del campo de fotos (ej. "#producto-fotosnuevas")
 */

use yii\helpers\Html;

?>
<label class="btn btn-primario btn-camara mb-2">
    <i class="bi bi-camera"></i> Sacar foto
    <input type="file" accept="image/*" capture="environment" class="d-none" data-camara-para="<?= Html::encode($destino) ?>">
</label>

<?php

/**
 * Acerca de: cada párrafo con el texto a la izquierda y sus fotos a la derecha,
 * ajustadas a la altura del texto. En el celular se apilan.
 *
 * @var yii\web\View $this
 * @var app\models\AcercaParrafo[] $parrafos
 */

use app\components\Fotos;
use app\models\Emprendimiento;
use yii\helpers\Html;

$sitio = Emprendimiento::actual();
$this->title = 'Acerca de';
if (isset($parrafos[0]->fotos[0])) {
    $this->params['og'] = ['image' => Fotos::urlAbsoluta($parrafos[0]->fotos[0]->archivo)];
}
?>
<div class="container contenido">
    <h1 class="titulo-pagina">Acerca de <?= Html::encode($sitio->nombre) ?></h1>

    <?php if (!$parrafos): ?>
        <div class="vacio">
            <i class="bi bi-card-text"></i>
            <p>Muy pronto vas a poder conocer más sobre nosotros.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($parrafos as $parrafo): ?>
        <?php $fotos = $parrafo->fotos; ?>
        <section class="acerca-parrafo <?= $fotos ? '' : 'sin-fotos' ?>">
            <div class="acerca-texto">
                <?php if ($parrafo->titulo): ?>
                    <h2><?= Html::encode($parrafo->titulo) ?></h2>
                <?php endif; ?>
                <p><?= nl2br(Html::encode($parrafo->texto)) ?></p>
            </div>
            <?php if ($fotos): ?>
                <div class="acerca-fotos">
                    <div class="acerca-fotos-grilla cantidad-<?= min(count($fotos), 4) ?>">
                        <?php foreach ($fotos as $foto): ?>
                            <img src="<?= Html::encode(Fotos::url($foto->archivo)) ?>" alt="" loading="lazy">
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</div>

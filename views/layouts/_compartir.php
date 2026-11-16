<?php

/**
 * Botones para compartir un link: WhatsApp, Facebook, copiar y el menú de compartir
 * del celular (aparece solo si el navegador lo tiene).
 *
 * @var yii\web\View $this
 * @var string $url     dirección absoluta
 * @var string $texto   mensaje que acompaña al link
 * @var string $titulo  título corto (para el menú del celular)
 * @var string $clase   clases extra del contenedor
 * @var string|null $foto  foto para mandar como archivo con el menú de compartir del celular
 */

use yii\helpers\Html;

$clase ??= '';
$foto ??= null;
$mensaje = trim($texto . "\n" . $url);
?>
<div class="compartir <?= Html::encode($clase) ?>">
    <?= Html::a('<i class="bi bi-whatsapp"></i> WhatsApp', 'https://wa.me/?text=' . rawurlencode($mensaje),
        ['class' => 'btn btn-sm btn-compartir whatsapp', 'target' => '_blank', 'rel' => 'noopener', 'title' => 'Compartir por WhatsApp']) ?>
    <?= Html::a('<i class="bi bi-facebook"></i>', 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url),
        ['class' => 'btn btn-sm btn-compartir facebook', 'target' => '_blank', 'rel' => 'noopener', 'title' => 'Compartir en Facebook', 'aria-label' => 'Compartir en Facebook']) ?>
    <button type="button" class="btn btn-sm btn-compartir" data-copiar="<?= Html::encode($url) ?>" title="Copiar el link">
        <i class="bi bi-link-45deg"></i> Copiar link
    </button>
    <button type="button" class="btn btn-sm btn-compartir d-none" data-compartir-nativo
            data-url="<?= Html::encode($url) ?>" data-titulo="<?= Html::encode($titulo) ?>" data-texto="<?= Html::encode($texto) ?>"
            <?php if ($foto): ?>data-foto="<?= Html::encode($foto) ?>"<?php endif; ?>
            title="Más opciones" aria-label="Más opciones para compartir">
        <i class="bi bi-share"></i><span class="etiqueta-foto d-none"> Con foto</span>
    </button>
</div>

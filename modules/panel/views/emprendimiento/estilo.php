<?php

/**
 * Selector del estilo del sitio: cada opción es una muestra pintada con las variables del tema.
 *
 * @var yii\web\View $this
 * @var string $actual
 */

use app\components\Tema;
use yii\helpers\Html;

$this->title = 'Estilo del sitio';
$this->params['temasFuentes'] = array_keys(Tema::lista());
?>
<div class="encabezado-panel">
    <h1><i class="bi bi-palette"></i> Estilo del sitio</h1>
</div>
<p class="text-body-secondary">El estilo que elijas lo ven todos los visitantes del catálogo.</p>

<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3">
    <?php foreach (Tema::lista() as $clave => $tema): ?>
        <div class="col">
            <?= Html::beginForm(['tema'], 'post') ?>
            <?= Html::hiddenInput('tema', $clave) ?>
            <button type="submit" class="muestra-tema <?= $clave === $actual ? 'elegido' : '' ?>" data-tema="<?= $clave ?>"
                    data-bs-theme="<?= $tema['oscuro'] ? 'dark' : 'light' ?>" style="--fuente-titulos: '<?= Html::encode($tema['fuente']) ?>'"
                    <?= $clave === $actual ? 'aria-current="true"' : '' ?>>
                <span class="muestra-barra"><i class="bi <?= $tema['icono'] ?>"></i> <?= Html::encode($tema['nombre']) ?></span>
                <span class="muestra-cuerpo">
                    <span class="muestra-tarjeta"><span></span><b>Producto</b><em>$ 12.500</em></span>
                    <span class="muestra-tarjeta"><span></span><b>Producto</b><em>$ 8.900</em></span>
                </span>
                <span class="muestra-pie">
                    <?= $clave === $actual ? '<i class="bi bi-check-circle-fill"></i> En uso' : 'Usar este estilo' ?>
                </span>
            </button>
            <?= Html::endForm() ?>
        </div>
    <?php endforeach; ?>
</div>

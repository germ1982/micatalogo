<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $productos
 * @var array $categorias [id => nombre]
 * @var string $q
 * @var int|null $categoria
 * @var string $orden
 */

use app\components\Fotos;
use app\models\Emprendimiento;
use app\models\Producto;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

$sitio = Emprendimiento::actual();
$logueado = !Yii::$app->user->isGuest;
$filtrando = $q !== '' || $categoria !== null;
$urlCatalogo = Url::home(true);
$this->params['og'] = ['url' => $urlCatalogo];
?>
<section class="banner <?= $sitio->banner ? 'con-imagen' : '' ?>"
    <?php if ($sitio->banner): ?>style="background-image: url('<?= Html::encode(Fotos::url($sitio->banner)) ?>')"<?php endif; ?>>
    <div class="container banner-texto">
        <h1><?= Html::encode($sitio->nombre) ?></h1>
        <?php if ($sitio->eslogan): ?>
            <p class="lead"><?= Html::encode($sitio->eslogan) ?></p>
        <?php endif; ?>
    </div>
</section>

<div class="container contenido-catalogo">
    <?= Html::beginForm(['/site/index'], 'get', ['class' => 'barra-filtros', 'role' => 'search', 'data-filtros' => true]) ?>
        <div class="filtro-buscar">
            <i class="bi bi-search"></i>
            <?= Html::textInput('q', $q, ['class' => 'form-control', 'placeholder' => 'Buscar por nombre, talle, color, categoría…',
                'aria-label' => 'Buscar en el catálogo', 'maxlength' => 100, 'type' => 'search']) ?>
        </div>
        <?php if ($categorias): ?>
            <label class="filtro-campo">
                <span><i class="bi bi-tags"></i> Categoría</span>
                <?= Html::dropDownList('categoria', $categoria, $categorias, ['class' => 'form-select', 'prompt' => 'Todas']) ?>
            </label>
        <?php endif; ?>
        <label class="filtro-campo">
            <span><i class="bi bi-sort-down"></i> Ordenar</span>
            <?= Html::dropDownList('orden', $orden, Producto::ORDENES, ['class' => 'form-select']) ?>
        </label>
        <button type="submit" class="btn btn-primario"><i class="bi bi-search"></i><span class="d-md-none"> Buscar</span></button>
    <?= Html::endForm() ?>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="text-body-secondary small">
            <?= $productos->totalCount === 1 ? '1 publicación' : $productos->totalCount . ' publicaciones' ?>
            <?php if ($filtrando): ?>
                · <?= Html::a('<i class="bi bi-x-circle"></i> Quitar filtros', ['/site/index', 'orden' => $orden !== 'recientes' ? $orden : null]) ?>
            <?php endif; ?>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <?php if ($logueado): ?>
                <?= Html::a('<i class="bi bi-plus-lg"></i> Nueva publicación', ['/panel/publicacion/crear', 'volver' => Yii::$app->request->url],
                    ['class' => 'btn btn-primario btn-sm']) ?>
            <?php endif; ?>
            <?= $this->render('/layouts/_compartir', [
                'url' => $urlCatalogo,
                'texto' => 'Mirá el catálogo de ' . $sitio->nombre,
                'titulo' => $sitio->nombre,
            ]) ?>
        </div>
    </div>

    <?php if ($productos->totalCount === 0): ?>
        <div class="vacio">
            <i class="bi bi-search"></i>
            <p><?= $filtrando ? 'No encontramos publicaciones con esa búsqueda.' : 'Todavía no hay publicaciones en el catálogo.' ?></p>
            <?php if ($filtrando): ?>
                <?= Html::a('Ver todo el catálogo', ['/site/index'], ['class' => 'btn btn-primario']) ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4">
            <?php foreach ($productos->getModels() as $producto): ?>
                <div class="col"><?= $this->render('_tarjeta', ['producto' => $producto, 'logueado' => $logueado]) ?></div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-center mt-4">
            <?= LinkPager::widget(['pagination' => $productos->pagination, 'maxButtonCount' => 5]) ?>
        </div>
    <?php endif; ?>
</div>

<?php

/**
 * @var yii\web\View $this
 * @var app\models\CategoriaBusqueda $busqueda
 * @var yii\data\ActiveDataProvider $proveedor
 */

use app\models\Categoria;
use yii\bootstrap5\LinkPager;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Categorías';
?>
<div class="encabezado-panel">
    <h1><i class="bi bi-tags"></i> Categorías</h1>
    <?= Html::a('<i class="bi bi-plus-lg"></i> Nueva categoría', ['crear'], ['class' => 'btn btn-primario']) ?>
</div>

<div class="caja p-0 p-md-2">
    <?= GridView::widget([
        'dataProvider' => $proveedor,
        'filterModel' => $busqueda,
        'tableOptions' => ['class' => 'table table-hover align-middle mb-0 grilla'],
        'options' => ['class' => 'table-responsive'],
        'pager' => ['class' => LinkPager::class, 'options' => ['class' => 'mt-3 px-2']],
        'summary' => '<div class="small text-body-secondary px-2 py-1">{totalCount} categorías</div>',
        'emptyText' => 'Todavía no hay categorías.',
        'columns' => [
            'nombre',
            [
                'attribute' => 'cantidad',
                'label' => 'Publicaciones',
                'format' => 'raw',
                'value' => fn(Categoria $c) => Html::a((int)$c->cantidad, ['/panel/publicacion/index', 'ProductoBusqueda[categoria_id]' => $c->id]),
            ],
            [
                'class' => ActionColumn::class,
                'header' => 'Acciones',
                'contentOptions' => ['class' => 'text-nowrap'],
                'template' => '{editar} {borrar}',
                'buttons' => [
                    'editar' => fn($url, Categoria $c) => Html::a('<i class="bi bi-pencil"></i>', ['editar', 'id' => $c->id],
                        ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Editar']),
                    'borrar' => fn($url, Categoria $c) => Html::a('<i class="bi bi-trash"></i>', ['borrar', 'id' => $c->id], [
                        'class' => 'btn btn-sm btn-outline-danger', 'title' => 'Borrar',
                        'data-method' => 'post',
                        'data-confirm' => '¿Borrar la categoría «' . $c->nombre . '»?'
                            . match ((int)$c->cantidad) {
                                0 => '',
                                1 => ' Su publicación queda sin categoría (no se borra).',
                                default => " Sus {$c->cantidad} publicaciones quedan sin categoría (no se borran).",
                            },
                    ]),
                ],
            ],
        ],
    ]) ?>
</div>

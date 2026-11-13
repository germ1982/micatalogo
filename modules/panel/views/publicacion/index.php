<?php

/**
 * @var yii\web\View $this
 * @var app\models\ProductoBusqueda $busqueda
 * @var yii\data\ActiveDataProvider $proveedor
 */

use app\components\Fotos;
use app\models\Categoria;
use app\models\Producto;
use yii\bootstrap5\LinkPager;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Publicaciones';
?>
<div class="encabezado-panel">
    <h1><i class="bi bi-grid-3x3-gap"></i> Publicaciones</h1>
    <?= Html::a('<i class="bi bi-plus-lg"></i> Nueva publicación', ['crear'], ['class' => 'btn btn-primario']) ?>
</div>

<div class="caja p-0 p-md-2">
    <?= GridView::widget([
        'dataProvider' => $proveedor,
        'filterModel' => $busqueda,
        'tableOptions' => ['class' => 'table table-hover align-middle mb-0 grilla'],
        'options' => ['class' => 'table-responsive'],
        'pager' => ['class' => LinkPager::class, 'options' => ['class' => 'mt-3 px-2']],
        'summary' => '<div class="small text-body-secondary px-2 py-1">Mostrando {begin}-{end} de {totalCount}</div>',
        'emptyText' => 'No hay publicaciones con esos filtros.',
        'columns' => [
            [
                'label' => 'Foto',
                'format' => 'raw',
                'contentOptions' => ['class' => 'celda-foto'],
                'value' => fn(Producto $p) => $p->fotoPrincipal
                    ? Html::img(Fotos::url($p->fotoPrincipal->miniatura), ['alt' => '', 'loading' => 'lazy'])
                    : '<span class="sin-foto"><i class="bi bi-image"></i></span>',
            ],
            [
                'attribute' => 'titulo',
                'format' => 'raw',
                'value' => fn(Producto $p) => Html::a(Html::encode($p->titulo), ['editar', 'id' => $p->id])
                    . ($p->detalles ? '<br><small class="text-body-secondary">' . Html::encode(implode(' · ', $p->detalles)) . '</small>' : ''),
            ],
            [
                'attribute' => 'categoria_id',
                'filter' => Categoria::lista(),
                'value' => fn(Producto $p) => $p->categoria->nombre ?? '—',
            ],
            [
                'attribute' => 'precio',
                'value' => fn(Producto $p) => $p->precioTexto,
                'contentOptions' => ['class' => 'text-nowrap'],
            ],
            [
                'attribute' => 'disponible',
                'filter' => [1 => 'Sí', 0 => 'No'],
                'format' => 'raw',
                'value' => fn(Producto $p) => Html::beginForm(['disponible', 'id' => $p->id], 'post')
                    . Html::hiddenInput('volver', Yii::$app->request->url)
                    . Html::submitButton($p->disponible ? '<i class="bi bi-eye"></i> Sí' : '<i class="bi bi-eye-slash"></i> No', [
                        'class' => 'btn btn-sm ' . ($p->disponible ? 'btn-success' : 'btn-outline-danger'),
                        'title' => $p->disponible ? 'Disponible. Clic para ocultarla al público' : 'No disponible. Clic para mostrarla',
                    ])
                    . Html::endForm(),
            ],
            [
                'attribute' => 'creado_en',
                'format' => 'date',
                'filterInputOptions' => ['class' => 'form-control', 'placeholder' => 'dd/mm/aaaa'],
            ],
            [
                'class' => ActionColumn::class,
                'header' => 'Acciones',
                'contentOptions' => ['class' => 'text-nowrap'],
                'template' => '{ver} {editar} {borrar}',
                'buttons' => [
                    'ver' => fn($url, Producto $p) => Html::a('<i class="bi bi-box-arrow-up-right"></i>', $p->url,
                        ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Ver en el catálogo', 'target' => '_blank']),
                    'editar' => fn($url, Producto $p) => Html::a('<i class="bi bi-pencil"></i>', ['editar', 'id' => $p->id],
                        ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Editar']),
                    'borrar' => fn($url, Producto $p) => Html::a('<i class="bi bi-trash"></i>', ['borrar', 'id' => $p->id], [
                        'class' => 'btn btn-sm btn-outline-danger', 'title' => 'Borrar',
                        'data-method' => 'post',
                        'data-confirm' => '¿Borrar «' . $p->titulo . '» con todas sus fotos? No se puede deshacer.',
                    ]),
                ],
            ],
        ],
    ]) ?>
</div>

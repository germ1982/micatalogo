<?php

/**
 * @var yii\web\View $this
 * @var app\models\UsuarioBusqueda $busqueda
 * @var yii\data\ActiveDataProvider $proveedor
 */

use app\models\Usuario;
use yii\bootstrap5\LinkPager;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Usuarios';
$yo = Yii::$app->user->id;
?>
<div class="encabezado-panel">
    <h1><i class="bi bi-people"></i> Usuarios</h1>
    <?= Html::a('<i class="bi bi-person-plus"></i> Nuevo usuario', ['crear'], ['class' => 'btn btn-primario']) ?>
</div>

<div class="caja p-0 p-md-2">
    <?= GridView::widget([
        'dataProvider' => $proveedor,
        'filterModel' => $busqueda,
        'tableOptions' => ['class' => 'table table-hover align-middle mb-0 grilla'],
        'options' => ['class' => 'table-responsive'],
        'pager' => ['class' => LinkPager::class, 'options' => ['class' => 'mt-3 px-2']],
        'summary' => '<div class="small text-body-secondary px-2 py-1">{totalCount} usuarios</div>',
        'columns' => [
            [
                'attribute' => 'usuario',
                'format' => 'raw',
                'value' => fn(Usuario $u) => Html::encode($u->usuario) . ($u->id === $yo ? ' <span class="badge text-bg-secondary">vos</span>' : ''),
            ],
            'nombre',
            [
                'attribute' => 'rol',
                'filter' => ['admin' => 'Administrador', 'editor' => 'Editor'],
                'value' => fn(Usuario $u) => $u->esAdmin() ? 'Administrador' : 'Editor',
            ],
            [
                'attribute' => 'activo',
                'filter' => [1 => 'Sí', 0 => 'No'],
                'format' => 'raw',
                'value' => fn(Usuario $u) => $u->activo
                    ? '<span class="badge text-bg-success">Sí</span>'
                    : '<span class="badge text-bg-danger">No</span>',
            ],
            [
                'attribute' => 'ultimo_ingreso',
                'format' => 'datetime',
                'filter' => false,
            ],
            [
                'class' => ActionColumn::class,
                'header' => 'Acciones',
                'contentOptions' => ['class' => 'text-nowrap'],
                'template' => '{editar} {borrar}',
                'buttons' => [
                    'editar' => fn($url, Usuario $u) => Html::a('<i class="bi bi-pencil"></i>', ['editar', 'id' => $u->id],
                        ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Editar']),
                    'borrar' => fn($url, Usuario $u) => $u->id === $yo ? '' : Html::a('<i class="bi bi-trash"></i>', ['borrar', 'id' => $u->id], [
                        'class' => 'btn btn-sm btn-outline-danger', 'title' => 'Borrar',
                        'data-method' => 'post',
                        'data-confirm' => '¿Borrar el usuario «' . $u->usuario . '»?',
                    ]),
                ],
            ],
        ],
    ]) ?>
</div>

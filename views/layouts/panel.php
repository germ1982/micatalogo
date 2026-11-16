<?php

/**
 * Layout del panel: menú de secciones a la izquierda (arriba en el celular) y el contenido.
 *
 * @var yii\web\View $this
 * @var string $content
 */

use yii\helpers\Html;

$usuario = Yii::$app->user->identity;
$actual = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
$secciones = [
    ['publicacion/', 'bi-grid-3x3-gap', 'Publicaciones', ['/panel/publicacion/index'], false],
    ['categoria/', 'bi-tags', 'Categorías', ['/panel/categoria/index'], true],
    ['usuario/', 'bi-people', 'Usuarios', ['/panel/usuario/index'], true],
    ['emprendimiento/index', 'bi-shop', 'Datos del emprendimiento', ['/panel/emprendimiento/index'], true],
    ['acerca/', 'bi-card-text', 'Acerca de', ['/panel/acerca/index'], true],
    ['emprendimiento/estilo', 'bi-palette', 'Estilo del sitio', ['/panel/emprendimiento/estilo'], true],
];
?>
<?php $this->beginContent('@app/views/layouts/main.php') ?>
<div class="container contenido">
    <div class="row g-4">
        <aside class="col-lg-3">
            <nav class="menu-panel">
                <div class="menu-panel-titulo d-none d-lg-block">
                    <i class="bi bi-speedometer2"></i> Panel de control
                    <small><?= Html::encode($usuario->nombre) ?> · <?= $usuario->esAdmin() ? 'Administrador' : 'Editor' ?></small>
                </div>
                <?php foreach ($secciones as [$prefijo, $icono, $nombre, $url, $soloAdmin]): ?>
                    <?php if (!$soloAdmin || $usuario->esAdmin()): ?>
                        <?= Html::a("<i class=\"bi $icono\"></i> $nombre", $url, [
                            'class' => 'menu-panel-enlace' . (str_starts_with($actual, $prefijo) ? ' activo' : ''),
                        ]) ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?= Html::a('<i class="bi bi-eye"></i> Ver el catálogo', ['/site/index'], ['class' => 'menu-panel-enlace']) ?>
            </nav>
        </aside>
        <div class="col-lg-9">
            <?php if ($usuario->esAdmin() && ($pendientes = \app\components\Migraciones::pendientes())): ?>
                <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span><i class="bi bi-database-exclamation"></i> Hay <?= count($pendientes) ?> actualizaciones de la base de datos pendientes.</span>
                    <?= Html::beginForm(['/panel/mantenimiento/aplicar'], 'post') ?>
                    <?= Html::submitButton('Aplicar ahora', ['class' => 'btn btn-sm btn-warning']) ?>
                    <?= Html::endForm() ?>
                </div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </div>
</div>
<?php $this->endContent() ?>

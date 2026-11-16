<?php

/**
 * @var yii\web\View $this
 * @var string $clave
 * @var array|null $resultado ['aplicadas' => string[], 'error' => string|null]
 * @var string[] $pendientes
 * @var bool $hayUsuarios
 */

use yii\helpers\Html;

$this->title = 'Mantenimiento';
?>
<h1 class="h4 mb-3">Actualizaciones de la base de datos</h1>

<?php if ($resultado !== null): ?>
    <?php if ($resultado['aplicadas']): ?>
        <div class="alert alert-success">
            Se aplicaron <?= count($resultado['aplicadas']) ?> actualizaciones:
            <ul class="mb-0 small"><?php foreach ($resultado['aplicadas'] as $m): ?><li><?= Html::encode($m) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <?php if ($resultado['error']): ?>
        <div class="alert alert-danger"><?= Html::encode($resultado['error']) ?></div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($pendientes): ?>
    <p>Hay <?= count($pendientes) ?> actualizaciones pendientes:</p>
    <ul class="small"><?php foreach ($pendientes as $m): ?><li><?= Html::encode($m) ?></li><?php endforeach; ?></ul>
    <?= Html::beginForm(['index', 'clave' => $clave], 'post') ?>
    <?= Html::submitButton('Aplicar actualizaciones', ['class' => 'btn btn-primary']) ?>
    <?= Html::endForm() ?>
<?php else: ?>
    <p class="text-success mb-3">✔ La base de datos está al día.</p>
    <?php if (!$hayUsuarios): ?>
        <?= Html::a('Crear el primer administrador', ['instalar', 'clave' => $clave], ['class' => 'btn btn-primary']) ?>
    <?php else: ?>
        <?= Html::a('Ir al sitio', Yii::$app->homeUrl, ['class' => 'btn btn-outline-secondary']) ?>
    <?php endif; ?>
<?php endif; ?>

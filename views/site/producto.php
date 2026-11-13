<?php

/**
 * @var yii\web\View $this
 * @var app\models\Producto $producto
 */

use app\components\Fotos;
use app\models\Emprendimiento;
use yii\helpers\Html;
use yii\helpers\StringHelper;

$sitio = Emprendimiento::actual();
$logueado = !Yii::$app->user->isGuest;
$url = $producto->getUrl(true);
$fotos = $producto->fotos;
$resumen = StringHelper::truncate(preg_replace('/\s+/u', ' ', (string)$producto->descripcion), 180);

$this->title = $producto->titulo;
$this->params['descripcion'] = $resumen ?: $producto->titulo;
$this->params['og'] = [
    'type' => 'product',
    'title' => $producto->titulo . ' · ' . $producto->precioTexto,
    'description' => $resumen ?: 'Mirá esta publicación en el catálogo de ' . $sitio->nombre,
    'url' => $url,
] + ($fotos ? ['image' => Fotos::urlAbsoluta($fotos[0]->archivo)] : []);
?>
<div class="container contenido">
    <nav aria-label="Ruta" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><?= Html::a('Catálogo', ['/site/index']) ?></li>
            <?php if ($producto->categoria): ?>
                <li class="breadcrumb-item"><?= Html::a(Html::encode($producto->categoria->nombre), ['/site/index', 'categoria' => $producto->categoria_id]) ?></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= Html::encode(StringHelper::truncate($producto->titulo, 40)) ?></li>
        </ol>
    </nav>

    <?php if ($logueado): ?>
        <div class="aviso-admin <?= $producto->disponible ? 'si' : 'no' ?>">
            <span>
                <i class="bi <?= $producto->disponible ? 'bi-eye' : 'bi-eye-slash' ?>"></i>
                <?= $producto->disponible ? 'Disponible: el público la ve.' : 'No disponible: el público no la ve.' ?>
            </span>
            <span class="d-flex gap-2">
                <?= Html::a('<i class="bi bi-pencil"></i> Editar', ['/panel/publicacion/editar', 'id' => $producto->id, 'volver' => Yii::$app->request->url],
                    ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <?= Html::beginForm(['/panel/publicacion/disponible', 'id' => $producto->id], 'post') ?>
                <?= Html::hiddenInput('volver', Yii::$app->request->url) ?>
                <?= Html::submitButton($producto->disponible ? 'Deshabilitar' : 'Habilitar',
                    ['class' => 'btn btn-sm ' . ($producto->disponible ? 'btn-outline-danger' : 'btn-outline-success')]) ?>
                <?= Html::endForm() ?>
            </span>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="galeria" data-galeria>
                <div class="galeria-principal">
                    <?php if ($fotos): ?>
                        <img src="<?= Html::encode(Fotos::url($fotos[0]->archivo)) ?>" alt="<?= Html::encode($producto->titulo) ?>" data-galeria-principal>
                    <?php else: ?>
                        <span class="sin-foto"><i class="bi bi-image"></i></span>
                    <?php endif; ?>
                </div>
                <?php if (count($fotos) > 1): ?>
                    <div class="galeria-miniaturas">
                        <?php foreach ($fotos as $i => $foto): ?>
                            <button type="button" class="<?= $i === 0 ? 'activa' : '' ?>" data-galeria-foto="<?= Html::encode(Fotos::url($foto->archivo)) ?>"
                                    aria-label="Ver foto <?= $i + 1 ?>">
                                <img src="<?= Html::encode(Fotos::url($foto->miniatura)) ?>" alt="" loading="lazy">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-5">
            <div class="ficha">
                <?php if ($producto->categoria): ?>
                    <span class="tarjeta-categoria"><?= Html::encode($producto->categoria->nombre) ?></span>
                <?php endif; ?>
                <h1 class="ficha-titulo"><?= Html::encode($producto->titulo) ?></h1>
                <div class="ficha-precio"><?= $producto->precioTexto ?></div>

                <?php if ($producto->detalles): ?>
                    <dl class="ficha-detalles">
                        <?php foreach ($producto->detalles as $nombre => $valor): ?>
                            <div><dt><?= $nombre ?></dt><dd><?= Html::encode($valor) ?></dd></div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>

                <?php if ($producto->descripcion): ?>
                    <div class="ficha-descripcion"><?= nl2br(Html::encode($producto->descripcion)) ?></div>
                <?php endif; ?>

                <?php if ($sitio->whatsapp): ?>
                    <?= Html::a('<i class="bi bi-whatsapp"></i> Consultar por WhatsApp',
                        $sitio->getWhatsappUrl("¡Hola! Me interesa «{$producto->titulo}»: $url"),
                        ['class' => 'btn btn-whatsapp btn-lg w-100 my-3', 'target' => '_blank', 'rel' => 'noopener']) ?>
                <?php endif; ?>

                <div class="mt-3">
                    <small class="text-body-secondary d-block mb-2">Compartir esta publicación</small>
                    <?= $this->render('/layouts/_compartir', [
                        'url' => $url,
                        'texto' => $producto->titulo . ' · ' . $producto->precioTexto,
                        'titulo' => $producto->titulo,
                    ]) ?>
                </div>
                <small class="text-body-secondary d-block mt-3">Publicado el <?= Yii::$app->formatter->asDate($producto->creado_en) ?></small>
            </div>
        </div>
    </div>
</div>

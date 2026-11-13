<?php

/** @var yii\web\View $this */

use app\models\Emprendimiento;
use yii\helpers\Html;

$sitio = Emprendimiento::actual();
$this->title = 'Contacto';

$datos = array_filter([
    ['bi-telephone', 'Teléfono', $sitio->telefono, $sitio->telefono ? 'tel:' . preg_replace('/[^\d+]/', '', $sitio->telefono) : null],
    ['bi-envelope', 'Correo', $sitio->email, $sitio->email ? 'mailto:' . $sitio->email : null],
    ['bi-geo-alt', 'Dirección', $sitio->direccion, $sitio->direccion ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($sitio->direccion) : null],
    ['bi-clock', 'Horario', $sitio->horario, null],
    ['bi-instagram', 'Instagram', $sitio->instagram ? '@' . $sitio->instagram : null, $sitio->instagram ? 'https://www.instagram.com/' . rawurlencode($sitio->instagram) . '/' : null],
    ['bi-facebook', 'Facebook', $sitio->facebook ? 'Nuestra página' : null, $sitio->facebook],
], fn($d) => $d[2] !== null && $d[2] !== '');
?>
<div class="container contenido">
    <h1 class="titulo-pagina">Contacto</h1>
    <div class="row g-4 justify-content-center">
        <?php if ($sitio->whatsapp): ?>
            <div class="col-lg-5">
                <div class="caja contacto-whatsapp">
                    <i class="bi bi-whatsapp"></i>
                    <h2>Escribinos por WhatsApp</h2>
                    <p>Consultas, precios, talles y envíos: te respondemos por ahí.</p>
                    <?= Html::a('<i class="bi bi-whatsapp"></i> Abrir WhatsApp',
                        $sitio->getWhatsappUrl('¡Hola! Vi el catálogo de ' . $sitio->nombre . ' y quería consultar.'),
                        ['class' => 'btn btn-whatsapp btn-lg', 'target' => '_blank', 'rel' => 'noopener']) ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="col-lg-7">
            <div class="caja">
                <?php if (!$datos): ?>
                    <p class="text-body-secondary mb-0">Todavía no hay datos de contacto cargados.</p>
                <?php endif; ?>
                <ul class="lista-contacto">
                    <?php foreach ($datos as [$icono, $nombre, $valor, $link]): ?>
                        <li>
                            <i class="bi <?= $icono ?>"></i>
                            <div>
                                <small><?= $nombre ?></small>
                                <?= $link ? Html::a(Html::encode($valor), $link, str_starts_with($link, 'http') ? ['target' => '_blank', 'rel' => 'noopener'] : [])
                                    : Html::encode($valor) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php

/** @var yii\web\View $this */
/** @var app\models\Emprendimiento $sitio */

use yii\helpers\Html;

$usuario = Yii::$app->user->identity;
?>
<footer id="footer" class="mt-auto py-3 pie">
    <div class="container">
        <div class="row align-items-center gy-2">
            <div class="col-md-5 text-center text-md-start">
                <?= Html::a('&copy; G.E.R.M. Servicios En Informática ' . date('Y'), 'https://germ1982.github.io/germweb/', [
                    'target' => '_blank', 'rel' => 'noopener', 'class' => 'pie-enlace',
                ]) ?>
            </div>
            <div class="col-md-7 text-center text-md-end">
                <?php if ($sitio->whatsapp): ?>
                    <?= Html::a('<i class="bi bi-whatsapp"></i> WhatsApp', $sitio->getWhatsappUrl('¡Hola! Vi el catálogo de ' . $sitio->nombre . ' y quería consultar.'),
                        ['class' => 'pie-enlace', 'target' => '_blank', 'rel' => 'noopener']) ?>
                    &nbsp;|&nbsp;
                <?php endif; ?>
                <?php if ($sitio->instagram): ?>
                    <?= Html::a('<i class="bi bi-instagram"></i> Instagram', 'https://www.instagram.com/' . rawurlencode($sitio->instagram) . '/',
                        ['class' => 'pie-enlace', 'target' => '_blank', 'rel' => 'noopener']) ?>
                    &nbsp;|&nbsp;
                <?php endif; ?>
                <?= Html::a('Contacto', ['/site/contacto'], ['class' => 'pie-enlace']) ?>
                &nbsp;|&nbsp;
                <?php if ($usuario === null): ?>
                    <?= Html::a('<i class="bi bi-person-lock"></i> Ingresar', ['/site/ingresar'], ['class' => 'pie-enlace', 'rel' => 'nofollow']) ?>
                <?php else: ?>
                    <span class="pie-usuario"><i class="bi bi-person-check"></i> <?= Html::encode($usuario->nombre) ?></span>
                    <?= Html::beginForm(['/site/salir'], 'post', ['class' => 'd-inline']) ?>
                    <?= Html::submitButton('Salir', ['class' => 'btn btn-link pie-enlace p-0 align-baseline']) ?>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

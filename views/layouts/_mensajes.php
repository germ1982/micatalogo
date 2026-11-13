<?php

/**
 * Mensajes flash con SweetAlert (como en Comunidad del Valle y Tu Tienda).
 * Los textos los arma el propio sistema: los datos del usuario ya van escapados.
 *
 * @var yii\web\View $this
 */

use yii\helpers\Json;

$tipos = [
    'success' => ['success', '¡Listo!'],
    'error' => ['error', 'Error'],
    'danger' => ['error', 'Error'],
    'warning' => ['warning', 'Atención'],
    'info' => ['info', 'Información'],
];

foreach (Yii::$app->session->getAllFlashes(true) as $tipo => $mensajes) {
    [$icono, $titulo] = $tipos[$tipo] ?? $tipos['info'];
    foreach ((array)$mensajes as $mensaje) {
        $datos = Json::htmlEncode(['icon' => $icono, 'title' => $titulo, 'html' => $mensaje]);
        $this->registerJs("Catalogo.mensaje($datos);");
    }
}

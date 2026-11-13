<?php

if (PHP_VERSION_ID < 80100) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Mi Catalogo necesita PHP 8.1 o más nuevo. Este servidor tiene PHP ' . PHP_VERSION . '.');
}

$env = require __DIR__ . '/../config/env.php';

if (empty($env['DB_NAME']) || empty($env['COOKIE_KEY'])) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    exit('El sitio está en mantenimiento. Volvé a intentar en unos minutos.');
}

defined('YII_DEBUG') or define('YII_DEBUG', !empty($env['DEBUG']));
defined('YII_ENV') or define('YII_ENV', YII_DEBUG ? 'dev' : 'prod');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();

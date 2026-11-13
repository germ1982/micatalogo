<?php

/** @var array $env datos de .env, cargados en web/index.php */

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

// El sitio se sirve desde la raíz del proyecto aunque index.php esté en web/
// (lo hace el .htaccess de la raíz), así que las URLs no llevan "/web".
$baseUrl = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/web/index.php'))), '/');

$config = [
    'id' => 'micatalogo',
    'name' => 'Mi Catálogo',
    'basePath' => dirname(__DIR__),
    'language' => 'es',
    'sourceLanguage' => 'es',
    'timeZone' => 'America/Argentina/Buenos_Aires',
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => $env['COOKIE_KEY'] ?? '',
            'baseUrl' => $baseUrl,
            // Detrás de un túnel o proxy local (cloudflared) los links salen con https y la IP real
            'trustedHosts' => ['127.0.0.1', '::1'],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        // Formatos fijos: no dependen de la extensión intl (sin ella los meses salen en inglés)
        'formatter' => [
            'defaultTimeZone' => 'America/Argentina/Buenos_Aires',
            'timeZone' => 'America/Argentina/Buenos_Aires',
            'dateFormat' => 'php:d/m/Y',
            'datetimeFormat' => 'php:d/m/Y H:i',
            'timeFormat' => 'php:H:i',
            'thousandSeparator' => '.',
            'decimalSeparator' => ',',
            'booleanFormat' => ['No', 'Sí'],
            'nullDisplay' => '—',
        ],
        // ?v=fecha del archivo: al cambiar el CSS/JS los navegadores bajan la versión nueva
        'assetManager' => [
            'appendTimestamp' => true,
        ],
        'user' => [
            'identityClass' => 'app\models\Usuario',
            'enableAutoLogin' => true,
            'loginUrl' => ['/site/ingresar'],
            'identityCookie' => ['name' => '_micatalogo_id', 'httpOnly' => true, 'sameSite' => 'Lax'],
        ],
        'session' => [
            'name' => 'micatalogo_sesion',
            'cookieParams' => ['httponly' => true, 'samesite' => 'Lax'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'except' => ['yii\web\HttpException:404'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'acerca' => 'site/acerca',
                'contacto' => 'site/contacto',
                'ingresar' => 'site/ingresar',
                'salir' => 'site/salir',
                'producto/<id:\d+>-<slug:[\w\-]*>' => 'site/producto',
                'producto/<id:\d+>' => 'site/producto',

                'panel' => 'panel/publicacion/index',
                'panel/<controller:[\w\-]+>' => 'panel/<controller>/index',
                'panel/<controller:[\w\-]+>/<action:[\w\-]+>' => 'panel/<controller>/<action>',
            ],
        ],
    ],
    'modules' => [
        'panel' => ['class' => 'app\modules\panel\Module'],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = ['class' => 'yii\debug\Module'];
}

return $config;

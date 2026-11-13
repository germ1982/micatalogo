<?php

/**
 * Lee .env (formato ini) y devuelve sus valores.
 * Lo usan la web (web/index.php) y la consola (yii).
 */
$archivo = dirname(__DIR__) . '/.env';
$env = is_file($archivo) ? parse_ini_file($archivo) : false;

return is_array($env) ? $env : [];

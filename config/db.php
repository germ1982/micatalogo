<?php

/** @var array $env */

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=' . ($env['DB_HOST'] ?? 'localhost') . ';dbname=' . ($env['DB_NAME'] ?? 'micatalogo'),
    'username' => $env['DB_USERNAME'] ?? 'root',
    'password' => $env['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
    'enableSchemaCache' => !YII_DEBUG,
    'schemaCacheDuration' => 3600,
];

<?php

use yii\db\Connection;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
return [
    'class' => Connection::class,
    'dsn' => 'mysql:host='.$_ENV['DB_HOST'].';dbname='.$_ENV['DB_NAME'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => 'utf8',
    'schemaMap' => [
        'mysql' => SamIT\Yii2\MariaDb\Schema::class
    ]

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];

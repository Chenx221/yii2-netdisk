<?php

use yii\db\Connection;

return [
    'class' => Connection::class,
    'dsn' => 'mysql:host=localhost:3307;dbname=yii2basic',
    'username' => 'root',
    'password' => 'chenx221',
    'charset' => 'utf8',
    'schemaMap' => [
        'mysql' => SamIT\Yii2\MariaDb\Schema::class
    ]

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];

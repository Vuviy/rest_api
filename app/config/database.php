<?php
declare(strict_types=1);

return [
    'default' => 'mysql',

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../storage/database.sqlite',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'],
            'dbname' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => 'pgsql',
            'dbname' => 'app',
            'user' => 'app',
            'password' => 'secret',
        ],
    ],
];
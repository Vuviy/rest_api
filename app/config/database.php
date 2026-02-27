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
            'host' => getenv('DB_HOST'),
            'dbname' => getenv('DB_NAME'),
            'user' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
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
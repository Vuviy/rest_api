<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap.php';

use App\Response;
use App\Router;
use App\Exception\HttpException;

$router = new Router($container);

require __DIR__ . '/routes/api.php';


try {
    $response = $router->dispatch();
} catch (HttpException $e) {
    $response = new Response(
        array_merge(
            ['error' => $e->getMessage()],
            $e->getPayload()
        ),
        $e->getStatus()
    );
} catch (\Throwable $e) {
    $response = new Response(
        ['error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile()],
        500
    );
}

$response->send();
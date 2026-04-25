<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/registry_exceptions.php';

use App\Exception\ExceptionHandler;
use App\Router;


if ($_SERVER['REQUEST_URI'] === '/favicon.ico') {
    return;
}


/**
 * @var $containerRoot \App\Container
 */
$router = new Router($containerRoot);


require __DIR__ . '/routes/api.php';

try {
    $response = $router->dispatch();
} catch (\Throwable $e) {
    $exeptionHandler = $containerRoot->get(ExceptionHandler::class);
    $response = $exeptionHandler->handle($e);
}

$response->send();
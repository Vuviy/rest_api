<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap.php';

use App\Controller\Security\AuthController;
use App\Exception\ExceptionHandler;
use App\Router;
use App\Security\Repositories\BlacklistRepository;
use App\Security\Services\JwtService;
use App\Security\Services\TokenService;
use App\Security\TokenFactory;

$router = new Router($containerRoot);

require __DIR__ . '/routes/api.php';

try {
    $response = $router->dispatch();
} catch (\Throwable $e) {
    $exeptionHandler = $containerRoot->get(ExceptionHandler::class);
    $response = $exeptionHandler->handle($e);
}

$response->send();
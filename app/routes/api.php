<?php

declare(strict_types=1);


/** @var \App\Router $router */


use App\Controller\BookController;
use App\Controller\Security\AuthController;
use App\Security\Middleware\JwtMiddleware;
use App\Security\Middleware\RateLimitMiddleware;


$router->get('/api/v1/books', [BookController::class, 'list'], [JwtMiddleware::class, RateLimitMiddleware::class]);
$router->get('/api/v1/books/{id}', [BookController::class, 'getById'], [JwtMiddleware::class]);
$router->post('/api/v1/books', [BookController::class, 'store'], [JwtMiddleware::class]);
$router->put('/api/v1/books/{id}', [BookController::class, 'update'], [JwtMiddleware::class]);
$router->patch('/api/v1/books/{id}', [BookController::class, 'patch'], [JwtMiddleware::class]);
$router->delete('/api/v1/books/{id}', [BookController::class, 'destroy'], [JwtMiddleware::class]);


//security
$router->post('/api/v1/auth', [AuthController::class, 'auth']);
$router->post('/api/v1/refresh', [AuthController::class, 'refresh']);
//security
<?php

declare(strict_types=1);


/** @var \App\Router $router */

use App\Controller\BookController;



$router->get('/api/v1/books', [BookController::class, 'index']);
$router->get('/api/v1/books/{id}', [BookController::class, 'getById']);
$router->post('/api/v1/books', [BookController::class, 'store']);
$router->put('/api/v1/books/{id}', [BookController::class, 'update']);
$router->patch('/api/v1/books/{id}', [BookController::class, 'patch']);
$router->delete('/api/v1/books/{id}', [BookController::class, 'destroy']);
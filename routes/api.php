<?php

declare(strict_types=1);


/** @var \App\Router $router */


use App\Controller\BookController;
use App\Controller\Security\AuthController;
use App\Security\Middleware\JwtMiddleware;
use App\Security\Middleware\RateLimitMiddleware;
use App\Versioning\VersionMiddleware;
use App\Versioning\DeprecationMiddleware;


/*
 * Book routes are registered for every version family:
 *   /api/v1/...  and  /api/v2/...  → URL versioning (version comes from the path)
 *   /api/...                       → neutral path  (version comes from X-API-Version / Accept)
 *
 * VersionMiddleware (first) resolves the version onto the request for ALL families, so
 * DeprecationMiddleware (last) can emit Deprecation/Sunset headers uniformly. One controller
 * method serves every version — the version travels on the request attribute — so there is
 * no per-version handler duplication. Add v3 by appending one prefix below.
 */
$versionPrefixes = ['/api/v1', '/api/v2', '/api'];

$bookRoutes = [
    ['get',    '/books',      'list',    [RateLimitMiddleware::class]],
    ['get',    '/books/{id}', 'getById', []],
    ['post',   '/books',      'store',   []],
    ['put',    '/books/{id}', 'update',  []],
    ['patch',  '/books/{id}', 'patch',   []],
    ['delete', '/books/{id}', 'destroy', []],
];

foreach ($versionPrefixes as $prefix) {
    foreach ($bookRoutes as [$method, $suffix, $action, $extra]) {
        $router->$method(
            $prefix . $suffix,
            [BookController::class, $action],
            [VersionMiddleware::class, JwtMiddleware::class, ...$extra, DeprecationMiddleware::class],
        );
    }
}


//security
$router->post('/api/auth', [AuthController::class, 'auth']);
$router->post('/api/refresh', [AuthController::class, 'refresh']);
//security

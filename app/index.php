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

//$middlewares = [
//    function ($request, callable $next) {
//        $request['srata'] = 5555;
//        echo 'Mid 1';
//        return $next($request);
//    },
//    function ($request, callable $next) {
//        echo 'Mid 2';
//        return $next($request);
//    }
//];
//
//
//$controller = function ($req) {
//    echo "Controller\n";
//    return "Response";
//};
//
//$request = ['get' => 'ddddd'];
//
//$runner = function ($index, $request) use (&$runner, $middlewares, $controller) {
//
//    if (!array_key_exists($index, $middlewares)) {
//        return $controller($request);
//    }
//    $middleware = $middlewares[$index];
//    return $middleware($request, fn($req) => $runner($index + 1, $req));
//};
//$runner(0, $request);
//dd($request);


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
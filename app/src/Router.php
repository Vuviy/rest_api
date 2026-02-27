<?php

namespace App;

use App\Enums\HttpStatus;

final class Router
{
    private array $routes = [];

    public function __construct(private Container $container)
    {
    }

    public function get(string $uri, array $action, array $middlewares = []): void
    {
        $this->routes['GET'][] = [
            'uri' => $uri,
            'action' => $action,
            'middlewares' => $middlewares,
        ];
    }

    public function post(string $uri, array $action, array $middlewares = []): void
    {
        $this->routes['POST'][] = [
            'uri' => $uri,
            'action' => $action,
            'middlewares' => $middlewares,
        ];
    }

    public function put(string $uri, array $action, array $middlewares = []): void
    {
        $this->routes['PUT'][] = [
            'uri' => $uri,
            'action' => $action
        ];
    }

    public function patch(string $uri, array $action, array $middlewares = []): void
    {
        $this->routes['PATCH'][] = [
            'uri' => $uri,
            'action' => $action,
            'middlewares' => $middlewares,
        ];
    }

    public function delete(string $uri, array $action, array $middlewares = []): void
    {
        $this->routes['DELETE'][] = [
            'uri' => $uri,
            'action' => $action,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(): Response
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (!isset($this->routes[$method])) {
            return new Response('404 Not Found', HttpStatus::NOT_FOUND);
        }

        foreach ($this->routes[$method] as $route) {
            $pattern = $this->convertToRegex($route['uri']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                [$controller, $methodName] = $route['action'];
                $middlewares = $route['middlewares'] ?? [];

                $request = new Request();

                $coreHandler = function (Request $request) use ($controller, $methodName, $matches) {
                    $controllerInstance = $this->container->get($controller);
                    return $controllerInstance->$methodName($request, ...$matches);
                };

                $pipeline = $this->buildMiddlewarePipeline(
                    $middlewares,
                    $coreHandler
                );
                return $pipeline($request);
            }
        }

        return new Response('404 Not Found', HttpStatus::NOT_FOUND);
    }

    private function buildMiddlewarePipeline(array $middlewares, callable $coreHandler): callable
    {
        return array_reduce(
            array_reverse($middlewares),
            function ($next, $middlewareClass) {
                return function (Request $request) use ($middlewareClass, $next) {
                    $middleware = $this->container->get($middlewareClass);
                    return $middleware->handle($request, $next);
                };
            },
            $coreHandler
        );
    }

    private function convertToRegex(string $uri): string
    {
        $pattern = preg_replace('#\{[^}]+\}#', '([^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }
}

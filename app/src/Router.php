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



        if (!array_key_exists($method, $this->routes)) {
            return new Response('404 Not Found', HttpStatus::NOT_FOUND);
        }

        foreach ($this->routes[$method] as $route) {
            $pattern = $this->convertToRegex($route['uri']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                [$controllerClass, $methodName] = $route['action'];
                $middlewares = $route['middlewares'] ?? [];

                $request = new Request();

                $dispatcher = $this->container->get(MiddlewareDispatcher::class);

                $controller = function (Request $request) use ($controllerClass, $methodName, $matches) {
                    $instance = $this->container->get($controllerClass);
                    return $instance->$methodName($request, ...$matches);
                };

                return $dispatcher->dispatch($middlewares, $request, $controller);
            }
        }

        return new Response('404 Not Found', HttpStatus::NOT_FOUND);
    }

    private function convertToRegex(string $uri): string
    {
        $pattern = preg_replace('#\{[^}]+\}#', '([^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }
}

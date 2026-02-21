<?php

namespace App;

final class Router
{
    private array $routes = [];

    public function __construct(private Container $container)
    {
    }


    public function get(string $uri, array $action): void
    {
        $this->routes['GET'][] = [
            'uri' => $uri,
            'action' => $action
        ];
    }

    public function post(string $uri, array $action): void
    {
        $this->routes['POST'][] = [
            'uri' => $uri,
            'action' => $action
        ];
    }

    public function put(string $uri, array $action): void
    {
        $this->routes['PUT'][] = [
            'uri' => $uri,
            'action' => $action
        ];
    }

    public function patch(string $uri, array $action): void
    {
        $this->routes['PATCH'][] = [
            'uri' => $uri,
            'action' => $action
        ];
    }

    public function delete(string $uri, array $action): void
    {
        $this->routes['DELETE'][] = [
            'uri' => $uri,
            'action' => $action
        ];
    }

    public function dispatch(): Response
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (!isset($this->routes[$method])) {
            return new Response('404 Not Found', 404);
        }

        foreach ($this->routes[$method] as $route) {
            $pattern = $this->convertToRegex($route['uri']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                [$controller, $methodName] = $route['action'];

                $controllerInstance = $this->container->get($controller);

                return $controllerInstance->$methodName(
                    new Request(),
                    ...$matches
                );
            }
        }

        return new Response('404 Not Found', 404);
    }

    private function convertToRegex(string $uri): string
    {
        $pattern = preg_replace('#\{[^}]+\}#', '([^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }
}

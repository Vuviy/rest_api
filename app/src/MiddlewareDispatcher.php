<?php

namespace App;

final class MiddlewareDispatcher
{
    public function __construct(
        private Container $container
    ) {
    }

    public function dispatch(
        array $middlewareClasses,
        Request $request,
        callable $controller
    ): Response {


        //callable

        $runner = function (int $index) use (
            &$runner,
            $middlewareClasses,
            $request,
            $controller
        ): Response {


            if (!array_key_exists($index, $middlewareClasses)) {
                return $controller($request);
            }

            $middlewareClass = $middlewareClasses[$index];
            $middleware = $this->container->get($middlewareClass);

            return $middleware->handle(
                $request,
                fn(Request $req) => $runner($index + 1)
            );
        };

        return $runner(0);
    }
}
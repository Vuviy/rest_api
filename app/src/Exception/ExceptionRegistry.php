<?php

namespace App\Exception;

use Throwable;

final class ExceptionRegistry
{
    private array $handlers = [];

    public function register(string $exceptionClass, callable $handler): void
    {
        $this->handlers[$exceptionClass] = $handler;
    }

    public function get(Throwable $e): ?callable
    {
        return $this->handlers[get_class($e)] ?? null;
    }

}
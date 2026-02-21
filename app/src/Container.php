<?php

namespace App;

use RuntimeException;

final class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (!array_key_exists($id, $this->bindings)) {
            throw new RuntimeException("Service {$id} not bound.");
        }

        $object = $this->bindings[$id]($this);

        $this->instances[$id] = $object;

        return $object;
    }
}

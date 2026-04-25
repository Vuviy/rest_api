<?php

namespace App;

interface RateLimiterInterface
{
    public function consume(string $key, int $tokens = 1): bool;

    public function getAvailableTokens(string $key): int;

    public function reset(string $key): void;
}
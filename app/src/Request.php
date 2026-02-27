<?php

namespace App;

use App\Exception\HttpException;
use Exception;

final class Request
{
    private array $headers;

    private array $attributes = [];

    public function __construct()
    {
        $this->headers = getallheaders() ?: [];
    }

    public function getHeader(string $name): ?string
    {
        $normalized = strtolower($name);

        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $normalized) {
                return $value;
            }
        }

        return null;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }
    public function post(string $key = null): mixed
    {
        return $this->getParsedBody($key);
    }

    public function get(string $key = null): mixed
    {
        if (null === $key) {
            return $_GET;
        }
        return $_GET[$key] ?? null;
    }

    private function getParsedBody(string $key = null): mixed
    {
        $input = file_get_contents('php://input');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        $data = [];
        if (str_contains($contentType, 'application/json')) {
            try {
                $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw new Exception('Invalid JSON', 400);
            }
        } elseif (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            parse_str($input, $data);
        }

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? null;
    }

    public function all(): array
    {
        return [...$_GET, ...$_POST, ...$_COOKIE, ...$_REQUEST, ...$_FILES, ...$_ENV, ...$_SERVER];
    }

    public function files(string $key): array
    {
        return $_FILES[$key] ?? [];
    }

    public function getJson(): array
    {
        return $this->getParsedBody();
    }
}

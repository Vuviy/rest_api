<?php

namespace App;

use App\Exception\HttpException;

final class Request
{
    public function post(string $key = null): mixed
    {
        if (null === $key) {
            return $_POST;
        }
        return $_POST[$key] ?? null;
    }

    public function get(string $key = null): mixed
    {
        if (null === $key) {
            return $_GET;
        }
        return $_GET[$key] ?? null;
    }

    public function put(string $key = null): mixed
    {
        return $this->getParsedBody($key, 'PUT');
    }

    public function patch(string $key = null): mixed
    {
        return $this->getParsedBody($key, 'PATCH');
    }

    public function delete(string $key = null): mixed
    {
        return $this->getParsedBody($key, 'DELETE');
    }

    private function getParsedBody(?string $key, string $method): mixed
    {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            return null;
        }

        $input = file_get_contents('php://input');

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        $data = [];
        if (str_contains($contentType, 'application/json')) {
            $data = json_decode($input, true) ?? [];
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
        $raw = file_get_contents('php://input');

        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new HttpException('Invalid JSON', 400);
        }
    }
}

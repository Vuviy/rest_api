<?php

namespace App;

use App\Enums\HttpStatus;

final class Response
{
    public function __construct(
        private array|string|null $data = null,
        private HttpStatus $status = HttpStatus::OK,
        private array $headers = [],
        private bool $raw = false
    ) {
    }

    /**
     * Build a response whose body is written verbatim (no JSON encoding and no
     * forced application/json header). Use it for non-JSON payloads such as
     * documentation or file exports; pass the desired Content-Type via $headers.
     */
    public static function raw(string $body, HttpStatus $status = HttpStatus::OK, array $headers = []): self
    {
        return new self($body, $status, $headers, raw: true);
    }

    public function send(): void
    {
        http_response_code($this->status->value);

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: no-referrer');
        header('X-XSS-Protection: 1; mode=block');

        foreach ($this->headers as $key => $value) {
            header(sprintf('%s: %s', $this->normalizeHeaderName($key), $value));
        }

        if ($this->raw) {
            if (is_string($this->data)) {
                echo $this->data;
            }

            return;
        }

        if ($this->data !== null) {
            header('Content-Type: application/json');
            echo json_encode($this->data, JSON_THROW_ON_ERROR);
        }
    }

    private function normalizeHeaderName(string $name): string
    {
        $name = str_replace('_', '-', $name);
        $name = strtolower($name);

        $parts = explode('-', $name);

        $parts = array_map(
            static fn(string $part) => ucfirst($part),
            $parts
        );

        return implode('-', $parts);
    }

    public function withAddedHeader(string $name, string $value): self
    {
        $clone = clone $this;

        if (isset($clone->headers[$name])) {
            $clone->headers[$name] .= ', ' . $value;
        } else {
            $clone->headers[$name] = $value;
        }

        return $clone;
    }
}

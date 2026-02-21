<?php

namespace App;

final class Response
{
    public function __construct(
        private array|string|null $data = null,
        private int $status = 200,
        private array $headers = []
    ) {
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        if ($this->data !== null) {
            header('Content-Type: application/json');
            echo json_encode($this->data, JSON_THROW_ON_ERROR);
        }
    }
}

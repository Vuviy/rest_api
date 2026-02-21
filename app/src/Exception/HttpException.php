<?php

namespace App\Exception;

use Exception;

class HttpException extends Exception
{
    public function __construct(
        string $message,
        int $status,
        private array $payload = []
    ) {
        parent::__construct($message, $status);
    }

    public function getStatus(): int
    {
        return $this->getCode();
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}

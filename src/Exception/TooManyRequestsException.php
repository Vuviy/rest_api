<?php

namespace App\Exception;

final class TooManyRequestsException extends \RuntimeException
{
    public function __construct(
        public readonly int $limit,
        public readonly int $remaining
    ) {
        parent::__construct('Too Many Requests ');
    }
}
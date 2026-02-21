<?php

namespace App\Exception;

class ValidationException extends HttpException
{
    public function __construct(array $errors)
    {
        parent::__construct(
            'Validation failed',
            422,
            ['details' => $errors]
        );
    }
}

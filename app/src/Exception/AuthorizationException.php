<?php

namespace App\Exception;

use Exception;

class AuthorizationException extends Exception
{
    public function __construct(string $message = 'Authorization error')
    {
        parent::__construct($message);
    }
}

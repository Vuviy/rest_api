<?php

namespace App\Exception;

use App\Enums\HttpStatus;
use App\Response;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Throwable;

final class ExceptionHandler
{
    public function __construct(private ExceptionRegistry $registry){}

    public function handle(Throwable $e): Response
    {
        $handler = $this->registry->get($e);

        if ($handler) {
            return $handler($e);
        }

        return $this->handleGeneric($e);
    }

    private function handleGeneric(Throwable $e): Response
    {
        return new Response(
            ['error' => 'Internal Server Error'],
            HttpStatus::SERVER_ERROR
        );
    }
}

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
    private array $map;

    public function __construct()
    {
        $this->map = [
            ValidationException::class => [$this, 'handleValidation'],
            NotFoundException::class   => [$this, 'handleNotFound'],
            AuthorizationException::class   => [$this, 'authorization'],
            ExpiredException::class   => [$this, 'expired'],
            SignatureInvalidException::class   => [$this, 'signatureInvalid'],
            BeforeValidException::class   => [$this, 'beforeValid'],
        ];
    }

    public function handle(Throwable $e): Response
    {

        $class = get_class($e);

        if (array_key_exists($class, $this->map)) {
            return call_user_func($this->map[$class], $e);
        }

        return $this->handleGeneric($e);
    }

    private function expired(ExpiredException $e): Response
    {
        return new Response(
            [
                'error'  => $e->getMessage(),
            ],
            HttpStatus::FORBIDDEN
        );
    }

    private function signatureInvalid(SignatureInvalidException $e): Response
    {
        return new Response(
            [
                'error'  => $e->getMessage(),
            ],
            HttpStatus::FORBIDDEN
        );
    }

    private function beforeValid(BeforeValidException $e): Response
    {
        return new Response(
            [
                'error'  => $e->getMessage(),
            ],
            HttpStatus::FORBIDDEN
        );
    }

    private function handleValidation(ValidationException $e): Response
    {
        return new Response(
            [
                'error'  => $e->getMessage(),
                'errors' => $e->getPayload()
            ],
            HttpStatus::VALIDATION_ERROR
        );
    }

    private function authorization(AuthorizationException $e): Response
    {
        return new Response(
            [
                'error'  => $e->getMessage(),
            ],
            HttpStatus::UNAUTHORIZED
        );
    }

    private function handleNotFound(NotFoundException $e): Response
    {
        return new Response(
            [
                'error' => $e->getMessage()
            ],
            HttpStatus::NOT_FOUND
        );
    }

    private function handleGeneric(Throwable $e): Response
    {
        return new Response(
            ['error' => 'Internal Server Error'],
            HttpStatus::SERVER_ERROR
        );
    }
}

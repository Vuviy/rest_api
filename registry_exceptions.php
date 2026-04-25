<?php
declare(strict_types=1);

use App\Enums\HttpStatus;
use App\Exception\AuthorizationException;
use App\Exception\ExceptionRegistry;
use App\Exception\NotFoundException;
use App\Exception\TooManyRequestsException;
use App\Exception\ValidationException;
use App\Response;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * @var $containerRoot \App\Container
 */
$registry = $containerRoot->get(ExceptionRegistry::class);


$registry->register(
    BeforeValidException::class,
    fn($e) => new Response(
        [
            'error'  => $e->getMessage(),
        ],
        HttpStatus::FORBIDDEN
    )
);

$registry->register(
    SignatureInvalidException::class,
    fn($e) => new Response(
        [
            'error'  => $e->getMessage(),
        ],
        HttpStatus::FORBIDDEN
    )
);

$registry->register(
    ExpiredException::class,
    fn($e) => new Response(
        [
            'error'  => $e->getMessage(),
        ],
        HttpStatus::FORBIDDEN
    )
);

$registry->register(
    AuthorizationException::class,
    fn($e) => new Response(
        [
            'error'  => $e->getMessage(),
        ],
        HttpStatus::UNAUTHORIZED
    )
);

$registry->register(
    NotFoundException::class,
    fn($e) => new Response(
        [
            'error' => $e->getMessage()
        ],
        HttpStatus::NOT_FOUND
    )
);

$registry->register(
    ValidationException::class,
    fn($e) => new Response(
        [
            'error'  => $e->getMessage(),
            'errors' => $e->getPayload()
        ],
        HttpStatus::VALIDATION_ERROR
    )
);

$registry->register(
    TooManyRequestsException::class,
    fn($e) => new Response(
        [
            'error'  => $e->getMessage(),
            'limit' => $e->limit,
            'remining' => $e->remaining,
        ],
        HttpStatus::TOO_MANY_REQUESTS
    )
);
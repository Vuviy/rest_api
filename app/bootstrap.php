<?php

declare(strict_types=1);

use App\Container;
use App\Controller\BookController;
use App\Controller\Security\AuthController;
use App\Database\Database;
use App\Exception\ExceptionHandler;
use App\Repositories\BookRepository;
use App\Security\Middleware\JwtMiddleware;
use App\Security\Repositories\BlacklistRepository;
use App\Security\Repositories\ClientsApiRepository;
use App\Security\Services\JwtService;
use App\Security\Services\TokenService;
use App\Security\TokenFactory;
use App\Service\BookService;
use App\Validators\AttributeValidator;
use App\Validators\BookListValidator;
use App\Validators\BookValidator;

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$containerRoot = new Container();

$containerRoot->bind(Database::class, fn() => new Database(config()));

$containerRoot->bind(BookRepository::class, fn($container) => new BookRepository($container->get(Database::class)));

$containerRoot->bind(BookService::class, fn($container) => new BookService($container->get(BookRepository::class)));

$containerRoot->bind(AttributeValidator::class, fn($container) => new AttributeValidator());

$containerRoot->bind(BookController::class, fn($container) => new BookController(
    $container->get(BookService::class),
    $container->get(BookValidator::class),
//    $container->get(BookListValidator::class),
    $container->get(AttributeValidator::class)
));

$containerRoot->bind(BookValidator::class, fn() => new BookValidator());

$containerRoot->bind(BookListValidator::class, fn() => new BookListValidator());

$containerRoot->bind(ExceptionHandler::class, fn($container) => new ExceptionHandler());


//Security

$containerRoot->bind(BlacklistRepository::class, fn($container) => new BlacklistRepository($container->get(Database::class)));
$containerRoot->bind(TokenFactory::class, fn($container) => new TokenFactory());

$containerRoot->bind(JwtService::class, fn($container) => new JwtService(
    __DIR__ . '/storage/keys/private.pem',
    __DIR__ . '/storage/keys/public.pem'
));

$containerRoot->bind(TokenService::class, fn($container) => new TokenService(
    $container->get(JwtService::class),
    $container->get(TokenFactory ::class),
    $container->get(BlacklistRepository::class),
));

$containerRoot->bind(AuthController::class, fn($container) => new AuthController(
    $container->get(TokenService::class),
    $container->get(ClientsApiRepository::class),
));

$containerRoot->bind(JwtMiddleware::class, fn($container) => new JwtMiddleware(
    $container->get(JwtService::class),
    $container->get(BlacklistRepository::class),
));


//Security

<?php

declare(strict_types=1);

use App\Container;
use App\Controller\BookController;
use App\Database\Database;
use App\Repositories\BookRepository;
use App\Service\BookService;
use App\Validators\BookListValidator;
use App\Validators\BookValidator;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$container = new Container();

$container->bind(Database::class, fn() =>
new Database(config())
);

$container->bind(BookRepository::class, fn($c) =>
new BookRepository($c->get(Database::class))
);

$container->bind(BookService::class, fn($c) =>
new BookService($c->get(BookRepository::class))
);

$container->bind(BookController::class, fn($c) =>
new BookController(
    $c->get(BookService::class),
    $c->get(BookValidator::class),
    $c->get(BookListValidator::class)
)
);

$container->bind(BookValidator::class, fn() =>
new BookValidator()
);

$container->bind(BookListValidator::class, fn() =>
new BookListValidator()
);

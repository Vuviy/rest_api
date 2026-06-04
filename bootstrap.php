<?php

declare(strict_types=1);

use App\Container;
use App\Controller\BookController;
use App\Controller\MigrationGuideController;
use App\Controller\Security\AuthController;
use App\Database\ConnectionFactory;
use App\Database\Database;
use App\Database\Drivers\MySqlDriver;
use App\Database\Drivers\PgSqlDriver;
use App\Database\Drivers\SqliteDriver;
use App\Database\QueryExecutor;
use App\Exception\ExceptionHandler;
use App\Exception\ExceptionRegistry;
use App\MiddlewareDispatcher;
use App\Redis\RedisRateLimiter;
use App\Repositories\BookRepository;
use App\Security\Middleware\JwtMiddleware;
use App\Security\Middleware\RateLimitMiddleware;
use App\Security\Repositories\BlacklistRepository;
use App\Security\Repositories\ClientsApiRepository;
use App\Security\Services\JwtService;
use App\Security\Services\TokenService;
use App\Security\TokenFactory;
use App\Service\BookService;
use App\Validators\AttributeValidator;
use App\Versioning\VersionResolver;
use App\Versioning\VersionMiddleware;
use App\Versioning\DeprecationMiddleware;
use App\Versioning\Transformers\BookTransformerFactory;

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

/*
 * class Container::class
 */
$containerRoot = new Container();


$containerRoot->bind(ConnectionFactory::class, function () {
    $factory = new ConnectionFactory();
    $factory->register(new PgSqlDriver());
    $factory->register(new MySqlDriver());
    $factory->register(new SqliteDriver());
    return $factory;
});

$containerRoot->bind(PDO::class, function ($container) {
    $config = configDb();
    $factory = $container->get(ConnectionFactory::class);
    $driverName = $config['connections'][$config['default']]['driver'];
    $settings = $config['connections'][$config['default']];
    return $factory->create($driverName, $settings);
});

$containerRoot->bind(QueryExecutor::class, fn($container) => new QueryExecutor($container->get(PDO::class)));

$containerRoot->bind(Database::class, fn($container) => new Database($container->get(QueryExecutor::class)));

$containerRoot->bind(BookRepository::class, fn($container) => new BookRepository($container->get(Database::class)));

$containerRoot->bind(BookService::class, fn($container) => new BookService($container->get(BookRepository::class)));

$containerRoot->bind(AttributeValidator::class, fn($container) => new AttributeValidator());

$containerRoot->bind(BookTransformerFactory::class, fn($container) => new BookTransformerFactory());

$containerRoot->bind(BookController::class, fn($container) => new BookController(
    $container->get(BookService::class),
    $container->get(AttributeValidator::class),
    $container->get(BookTransformerFactory::class),
));
$containerRoot->bind(MigrationGuideController::class, fn($container) => new MigrationGuideController(
    __DIR__ . '/docs',
));

$containerRoot->bind(ExceptionRegistry::class, fn($container) => new ExceptionRegistry());
$containerRoot->bind(ExceptionHandler::class, fn($container) => new ExceptionHandler($container->get(ExceptionRegistry::class)));


//Security

$containerRoot->bind(BlacklistRepository::class, fn($container) => new BlacklistRepository($container->get(Database::class)));
$containerRoot->bind(ClientsApiRepository::class, fn($container) => new ClientsApiRepository($container->get(Database::class)));
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

$containerRoot->bind(MiddlewareDispatcher::class, fn($container) => new MiddlewareDispatcher($containerRoot));


//Security

//Rate Limiting

$containerRoot->bind(RedisRateLimiter::class, fn($container) => new RedisRateLimiter(new Redis()));

$containerRoot->bind(RateLimitMiddleware::class, fn($container) => new RateLimitMiddleware(
    $container->get(RedisRateLimiter::class),
    rateLimitingConfig(),
));

//Rate Limiting


//Versioning

$containerRoot->bind(VersionResolver::class, function () {
    $config = versioningConfig();
    return new VersionResolver(
        $config['supported'],
        $config['default'],
        $config['media_type'],
    );
});

$containerRoot->bind(VersionMiddleware::class, fn($container) => new VersionMiddleware(
    $container->get(VersionResolver::class)
));

$containerRoot->bind(DeprecationMiddleware::class, function () {
    $config = versioningConfig();
    return new DeprecationMiddleware($config['deprecated']);
});

//Versioning

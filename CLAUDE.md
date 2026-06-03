# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A framework-free PHP 8.3 REST API for a book repository. No Laravel, Symfony, or any other framework — all core components (router, container, query builder, middleware pipeline) are hand-rolled. Runs inside Docker (Docker config lives in the sibling `docker_for_rest_api/` repo).

## Commands

All commands run inside the PHP container (`rest_api_php`):

```bash
# Install dependencies
docker exec rest_api_php composer install

# Static analysis (Psalm — level 1, strict)
docker exec rest_api_php vendor/bin/psalm

# Code style (PHP_CodeSniffer)
docker exec rest_api_php vendor/bin/phpcs src/

# Fix code style automatically
docker exec rest_api_php vendor/bin/phpcbf src/

# Static analysis (PHPStan alternative)
docker exec rest_api_php vendor/bin/phpstan analyse src/
```

There are no automated tests in this project.

## Architecture

### Request Lifecycle

`index.php` → `bootstrap.php` (DI bindings) → `registry_exceptions.php` (exception map) → `routes/api.php` (route definitions) → `Router::dispatch()` → `MiddlewareDispatcher::dispatch()` → Controller method → `Response::send()`

### Key Components

**Container** (`src/Container.php`) — simple singleton DI container. All bindings are registered manually in `bootstrap.php`. Every service is lazily instantiated and cached as a singleton.

**Router** (`src/Router.php`) — matches `{param}` path segments via regex. Route definitions pass an array of middleware class names; these are resolved from the container and run as a recursive chain by `MiddlewareDispatcher`.

**Middleware pipeline** (`src/MiddlewareDispatcher.php`) — recursive closure pattern. Each middleware calls `$next($request)` to advance. Currently used middleware: `JwtMiddleware` (validates RS256 Bearer token, checks blacklist) and `RateLimitMiddleware` (token-bucket via Redis).

**Database layer** — three-layer stack:
- `QueryExecutor` wraps `PDO` (executes raw SQL + bindings)
- `Database` wraps `QueryExecutor` (exposes `select/insert/update/delete`)
- `QueryBuilder` wraps `Database` (fluent builder; supports cursor pagination, subqueries, aggregates)

`ConnectionFactory` selects between MySQL, PostgreSQL, and SQLite drivers based on `SQL_DRIVER` env var.

**Validation** — PHP 8 attributes on DTO properties (`#[Required]`, `#[NotEmpty]`, `#[Min]`, `#[Max]`, `#[Choice]`, `#[Sortable]`, `#[Orderable]`). `AttributeValidator::validate($dto)` reflects over properties, invokes each attribute, and throws `ValidationException` with collected errors.

**Exception handling** — `ExceptionRegistry` maps exception class → response factory callable. Registered in `registry_exceptions.php`. `ExceptionHandler::handle()` looks up the class, falling back to a 500 response. To add handling for a new exception type, register it in `registry_exceptions.php`.

**JWT / Auth** — RS256 key pair stored in `storage/keys/`. `JwtService` signs/verifies tokens. `TokenService` manages access (15 min) + refresh (7 days) token lifecycle with rotation. Revoked tokens are blacklisted in Redis via `BlacklistRepository`.

**Rate limiting** — token-bucket algorithm in `RedisRateLimiter`. Config per HTTP method in `config/rate_limiting.php`. Applied via `RateLimitMiddleware` on routes that register it.

### Adding a New Route

1. Define the controller method.
2. Bind the controller in `bootstrap.php` if not already there.
3. Add the route in `routes/api.php`, passing the desired middleware classes.

### Environment Variables

Key `.env` variables: `SQL_DRIVER` (mysql/pgsql/sqlite), `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`. See `.env.example` for the full list.

### API / Docs

- Swagger UI: `http://localhost:8081`
- OpenAPI spec: `openapi.yaml` (root of repo)
- phpMyAdmin: `http://localhost:8000`



# Project Rules

## Language

* Always communicate with me in Ukrainian.
* Explain important architectural and implementation decisions.
* When implementing code, explain why this approach was chosen.
* If there are alternatives, describe trade-offs.

## Learning Mode

I am using this project to improve my backend engineering skills.

Before implementing:

* explain the problem
* explain possible approaches
* explain why the selected approach is preferred

After implementation:

* explain the execution flow
* explain design decisions
* explain potential drawbacks

## Code Quality

* Prefer readability over cleverness.
* Follow SOLID when appropriate.
* Avoid unnecessary abstractions.
* Explain every non-obvious pattern.

## Workflow

1. Analyze task.
2. Create implementation plan.
3. Explain plan.
4. Implement.
5. Review implementation.
6. Generate learning notes.

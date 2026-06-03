---
name: php-conventions
description: Code conventions of this framework-free PHP 8.3 REST API. Use whenever writing or reviewing PHP code in src/ — typing, structure, Psalm level 1, phpcs, project patterns.
---

# Project code conventions

## Base rules
- Every file starts with `declare(strict_types=1);`.
- Classes are `final` by default; `readonly` for DTO fields where appropriate.
- Full typing: parameters, returns, properties. Avoid `mixed` except where already used (`Request`).
- PSR-12 / PSR-4 (`App\` → `src/`). Namespace mirrors the path.

## Architectural patterns to stick to
- **DI via Container** (`src/Container.php`): new services are registered manually in `bootstrap.php`, lazy-singleton.
- **Middleware**: a new cross-cutting concern (auth, rate-limit, versioning) → a separate class implementing `MiddlewareInterface::handle(Request, callable $next)`.
- **Thin controllers**: business logic in `Service/`, data access in `Repositories/`.
- **Validation** via PHP attributes on DTOs + `AttributeValidator`.
- **Exceptions** → registered in `registry_exceptions.php` (map of class → Response factory).

## Quality tools (run inside the `rest_api_php` container)
```bash
docker exec rest_api_php vendor/bin/psalm        # static analysis, level 1 strict
docker exec rest_api_php vendor/bin/phpcs src/   # style
docker exec rest_api_php vendor/bin/phpcbf src/  # auto-fix style
```
Code is considered done only when psalm and phpcs are green.

## What to avoid
- Needless abstractions and a "framework inside a framework" (the project is deliberately framework-free).
- Logic in `index.php` / `routes/api.php` — those hold declarations only.
- Duplication: `grep` for an existing solution first, then write a new one.

## Useful facts about the current state
- `Router::put()` does NOT forward `$middlewares` (lines ~35–41) — a potential bug, account for it when working with the router.
- `Request::getHeader()` already exists — a ready foundation for header/Accept versioning.

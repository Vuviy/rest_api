# Tasks: API Versioning System

> Breakdown of `specs/api-versioning.spec.md`. Decisions: `decisions/0001-api-versioning-strategy.md`.
> Filled by backend-architect; executed by the main agent AFTER plan approval.

| # | Step | Files (expected) | Depends on | Status |
|---|------|------------------|------------|--------|
| 0 | Fix `Router::put()` so it forwards `$middlewares` (pre-work bug) | `src/Router.php` | — | ✅ |
| 1 | Version config: supported versions, fixed default, deprecated map, sunset dates, migration link | `config/versioning.php` + `functions/functions.php` accessor | — | ✅ |
| 2 | `VersionResolver` — pure logic: parse `X-API-Version`, parse `Accept;version`, apply priority, fall back to default for unknown | `src/Versioning/VersionResolver.php` | 1 | ✅ |
| 3 | Version middleware — calls resolver, stores `Request::setAttribute('api_version', ...)`, registered in `bootstrap.php` | `src/Versioning/VersionMiddleware.php`, `bootstrap.php` | 2 | ✅ |
| 4 | Deprecation/Sunset response middleware — adds headers via `Response::withAddedHeader()` after `$next` | `src/Versioning/DeprecationMiddleware.php`, `bootstrap.php` | 1, 3 | ✅ |
| 5 | Routing — register v2 versioned routes + neutral `/api/...` routes; map neutral+version → same handlers (no duplication); attach middleware. Also extended `VersionResolver` with URL priority | `routes/api.php`, `src/Versioning/VersionResolver.php` | 0, 3, 4 | ✅ |
| 6 | Migration guide v1 → v2 (also documents auth move to neutral `/api/auth`) | `docs/migration-v1-to-v2.md` | 5 | ✅ |
| 7 | Quality gates green | — | 0-6 | ☐ |
| 8 | Manual verification of all 3 strategies + v1 backward compatibility | — | 5 | ☐ |

## Execution order (why)
Bug fix (0) first — without it PUT routes silently skip versioning. Then config (1) as the source of
truth, then pure resolver (2) which is the easiest to reason about and test, then the middleware (3,4)
that wires resolver + headers into the pipeline, then routing (5) which depends on all of the above.
Docs and verification last.

## Quality gates (step 7)
```bash
docker exec rest_api_php vendor/bin/psalm
docker exec rest_api_php vendor/bin/phpcs src/
```

## Manual verification matrix (step 8)
| Case | Request | Expected |
|---|---|---|
| URL v1 | `GET /api/v1/books` | v1 result, unchanged (backward compat) |
| URL v2 | `GET /api/v2/books` | v2 result |
| Header | `GET /api/books` + `X-API-Version: 2` | v2 result |
| Accept | `GET /api/books` + `Accept: application/vnd.api+json;version=2` | v2 result |
| Conflict | `/api/books` + `X-API-Version: 1` + `Accept ...;version=2` | v1 (header wins) |
| No version | `GET /api/books` (no headers) | default (v1) |
| Unknown | `/api/books` + `X-API-Version: 99` | default + warning header |
| Deprecated | request a deprecated version | response carries `Deprecation` + `Sunset` |

## Definition of Done
- [ ] All steps ✅
- [ ] psalm + phpcs green
- [ ] All spec acceptance criteria met
- [ ] Review passed (`reviews/`)
- [ ] Learning note created (`learning/`)

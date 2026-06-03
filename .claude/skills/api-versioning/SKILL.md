---
name: api-versioning
description: Reference on REST API versioning strategies (URL, header, content negotiation), deprecation/sunset, backward compatibility. Use when designing or reviewing the API Versioning task.
---

# API Versioning — reference

## Three strategies (all required by the task)
| Strategy | Example | Pros | Cons |
|---|---|---|---|
| **URL path** | `GET /api/v2/books` | explicit, cacheable, easy to test | "pollutes" the URI, not REST-purist |
| **Header** | `X-API-Version: 2` | clean URI | invisible in browser, harder to debug |
| **Accept (content negotiation)** | `Accept: application/vnd.api+json;version=2` | canonical REST | complex parsing, poor DX |

**Resolution priority** (typical practice): explicit header → Accept → URL → default.
Record the chosen order in an ADR — this is the most important decision of the task.

## Deprecation & Sunset (RFC guidance)
- `Deprecation: true` or a date — marks a deprecated version (RFC 8594 / draft).
- `Sunset: <HTTP-date>` — the shutdown date of a version (RFC 8594).
- `Warning` or a custom `X-API-Deprecation` — human-readable message + link to the migration guide.
- Old versions must **keep working** until sunset (backward compatibility — a task criterion).

## Architecture hints for this project
- Version resolution is naturally a separate **VersionResolver** + **version middleware**
  that puts the version into `Request::setAttribute('api_version', ...)`.
- Routing: either separate route sets per version, or one route + dispatch by version in the controller.
  Record the trade-off in an ADR.
- Add deprecation/sunset headers centrally (response middleware), not in every controller.

## Acceptance (from the task)
All 3 strategies work · backward compatibility · deprecation notices · correct routing · migration guide.

## Anti-patterns
- A version "smeared" across controllers with if-statements.
- Breaking v1 when adding v2.
- Silently disabling a version with no sunset warning.

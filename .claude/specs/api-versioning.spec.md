# Specification: API Versioning System

> Filled in as an example/starting point. **These are requirements, not code.**
> To be refined by `backend-architect` before implementation.

## 1. Problem
The API only has a hardcoded v1 in `routes/api.php`. We need a versioning system
so we can evolve the API (v2+) without breaking existing clients.

## 2. Goals / Non-goals
- **Goals:** support 3 version-selection strategies; deprecation/sunset; correct routing; migration guide.
- **Non-goals:** rewriting the books business logic; changing the v1 data format.

## 3. Functional requirements
- [ ] URL versioning: `/api/v1/…`, `/api/v2/…`
- [ ] Header versioning: `X-API-Version: 2`
- [ ] Accept negotiation: `Accept: application/vnd.api+json;version=2`
- [ ] Deprecation warnings in response headers
- [ ] Sunset date for old versions
- [ ] Version routing middleware

## 4. Acceptance criteria
- [ ] All three strategies work and yield the same result for the same version
- [ ] Backward compatibility: existing v1 clients do not break
- [ ] Deprecation notices present for deprecated versions
- [ ] Routing correctly leads to the right version
- [ ] A migration guide exists (v1 → v2)

## 5. Edge-cases and risks
- Strategy conflict (URL says v1, header says v2) → a fixed priority is needed.
- Unknown/non-existent version → define behavior (400 or fallback to default).
- No version provided → default version.
- `Router::put()` does not forward middleware — the version middleware won't run there (a bug, account for it).

## 6. Impact on existing code
- `routes/api.php` (version prefixes), `src/Router.php` (resolution/matching),
  `src/MiddlewareDispatcher.php` (chain), `bootstrap.php` (registering new services),
  `src/Request.php` (already has `getHeader()`).

## 7. Backward compatibility
v1 stays working until the official sunset date; no changes to v1 contracts.

## 8. Open questions (engineer decides)
- Priority of resolution strategies?
- Separate route sets per version or dispatch within the controller?
- Behavior on unknown version: 400 or fallback?
- Which exact deprecation headers (`Deprecation`/`Sunset`/`Warning`/custom)?

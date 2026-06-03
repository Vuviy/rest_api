# API Migration Guide: v1 → v2

This guide explains how the API versioning works and how to migrate from **v1** to **v2**.

> **TL;DR**
> - v1 is **deprecated** and will be shut down (**sunset: 2026-12-31**).
> - Pick a version via the URL, the `X-API-Version` header, or `Accept` content negotiation.
> - Auth moved to the version-neutral `/api/auth` and `/api/refresh` (**breaking change** — see below).

---

## 1. Choosing an API version

There are three interchangeable strategies. They produce the same result for the same version.

### a) URL path (recommended — explicit and cacheable)
```
GET /api/v1/books
GET /api/v2/books
```
The version in the path is authoritative; request headers are ignored for version selection here.

### b) `X-API-Version` header (on the neutral path)
```
GET /api/books
X-API-Version: 2
```

### c) `Accept` content negotiation (on the neutral path)
```
GET /api/books
Accept: application/vnd.api+json;version=2
```

### Resolution priority (neutral path `/api/...`)
When more than one signal is present, the order is:

```
X-API-Version  →  Accept;version  →  default
```

So if you send both `X-API-Version: 1` and `Accept: ...;version=2`, you get **v1** (the explicit
header wins).

### Defaults and unknown versions
- **No version provided** on the neutral path → the **default version (v1)** is used. The default is
  fixed and will not jump to the newest version automatically.
- **Unknown/unsupported version** requested via header/Accept (e.g. `99`) → the request falls back to
  the **default version (v1)**.
- An unknown version in the **URL** (e.g. `/api/v99/books`) simply does **not exist** → `404 Not Found`.

---

## 2. Deprecation of v1

v1 is deprecated. Any response served as v1 (whether selected by URL, header, Accept, or default)
includes these headers:

```
Deprecation: true
Sunset: Thu, 31 Dec 2026 00:00:00 GMT
Link: <https://example.com/docs/api/migration/v1-to-v2>; rel="deprecation"
```

- `Deprecation: true` — this version is deprecated.
- `Sunset` — the date after which v1 may stop working (RFC 8594).
- `Link` — points to this migration guide.

**Action:** watch for these headers in your client and plan migration before the sunset date.

---

## 3. What changed in v2

For the book resource, **v2 currently behaves the same as v1** (same fields, same semantics).
v2 exists so the API can evolve without breaking v1 clients; future v2-only changes will be
documented here. Migrating now is low-risk and recommended ahead of the v1 sunset.

---

## 4. Breaking change: authentication is now version-neutral

Authentication is **not** a versioned resource (the token is the same across API versions), so it
moved out of the version path:

| Before (removed) | Now |
|---|---|
| `POST /api/v1/auth` | `POST /api/auth` |
| `POST /api/v1/refresh` | `POST /api/refresh` |

**Action required:** update your auth and token-refresh calls to the new neutral paths. The old
`/api/v1/auth` and `/api/v1/refresh` endpoints no longer exist and return `404`.

---

## 5. Migration checklist

- [ ] Point book calls at v2: `/api/v2/...`, or send `X-API-Version: 2` on `/api/...`.
- [ ] Update auth calls to `/api/auth` and `/api/refresh`.
- [ ] Stop relying on the implicit default; request a version explicitly.
- [ ] Monitor `Deprecation` / `Sunset` response headers.
- [ ] Complete migration before **2026-12-31**.

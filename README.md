# REST API

A fully featured REST API for a book repository built with **pure PHP** (no frameworks). Implements JWT authentication, rate limiting, pagination, filtering, sorting, and HATEOAS links.

---

## Features

### REST API
- Full CRUD via `GET`, `POST`, `PUT`, `PATCH`, `DELETE`
- JSON request/response with `Content-Type` headers
- HTTP status codes: `200`, `201`, `204`, `400`, `401`, `404`, `429`, `500`
- Pagination via `Link` headers (RFC 5988)
- Filtering: `?author=John&title=Laravel`
- Sorting: `?sort=title&orderBy=desc`
- HATEOAS: responses include links to related resources

### API Versioning
- Three interchangeable strategies: URL path (`/api/v1`, `/api/v2`), `X-API-Version` header, `Accept` content negotiation
- Supported versions: **v1, v2** — default **v1** (fixed, never auto-jumps to latest)
- Deprecation signalling via RFC 8594 `Deprecation` / `Sunset` / `Link` headers
- Per-version output representation (transformers) without touching controllers/services
- Self-served migration guide at `GET /docs/migration-v1-to-v2`

### JWT Authentication
- Access token: **15 minutes**, Refresh token: **7 days**
- RS256 algorithm with RSA key pair
- Token rotation on refresh
- Blacklisting of revoked tokens (Redis)
- Implemented with `firebase/php-jwt`

### Rate Limiting
- Token bucket algorithm
- Redis storage
- Different limits per HTTP method: `60/min` for GET, `10/min` for POST
- Per-user and per-IP limits
- Response headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`
- Returns `429 Too Many Requests` when exceeded

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.3 (no framework) |
| Database | MySQL 8.4 |
| Cache / Rate Limiting / Blacklist | Redis 8.4 |
| Web server | Nginx 1.27 |
| Auth | firebase/php-jwt (RS256) |
| API Docs | OpenAPI 3.0 / Swagger UI |
| Infrastructure | Docker (separate repo) |

---

## Project Structure

This repository contains only the PHP application code.
Docker configuration lives in a separate repository: [docker_for_rest_api](https://github.com/Vuviy/docker_for_rest_api)

```
rest_api/                ← this repo (PHP app)
docker_for_rest_api/     ← separate repo (Docker + docker-compose)
  ├── docker/
  ├── app/               ← clone this repo here
  ├── docker-compose.yml
  └── .env
```

---

## Getting Started

### Prerequisites

- [Docker](https://www.docker.com/) and Docker Compose installed
- Git

### Installation

**1. Clone the Docker repository**

```bash
git clone https://github.com/Vuviy/docker_for_rest_api.git
cd docker_for_rest_api
```

**2. Clone this repository into the `app/` folder**

```bash
git clone https://github.com/Vuviy/rest_api.git app
```

**3. Copy the environment file**

```bash
cp app/.env.example app/.env

cp .env.example .env
```

**4. Configure `app/.env` `.env` **

```env
DB_HOST=db_rest_api
DB_NAME=db_rest_api
DB_USER=root
DB_PASS=root
SQL_DRIVER=mysql
```

```env
DB_DATABASE=db_rest_api
MYSQL_ROOT_PASSWORD=root
```

**5. Start Docker containers**

```bash
docker compose up -d
```

**6. Install dependencies**

```bash
docker exec rest_api_php composer install
```

**7. Generate RSA keys for JWT**

```bash
docker exec rest_api_php mkdir -p storage/keys

docker exec rest_api_php openssl genpkey -algorithm RSA \
  -out storage/keys/private.pem -pkeyopt rsa_keygen_bits:4096

docker exec rest_api_php openssl rsa -pubout \
  -in storage/keys/private.pem -out storage/keys/public.pem
```

Set correct permissions:

```bash
docker exec rest_api_php chmod 600 storage/keys/private.pem
docker exec rest_api_php chmod 644 storage/keys/public.pem
```

> If you get a permission error reading the keys inside the container, the issue is that the file owner differs from the PHP process user. Fix it with:
> ```bash
> docker exec rest_api_php chown www-data:www-data storage/keys/private.pem storage/keys/public.pem
> ```
> Avoid using `chmod 777` in production — it makes private keys readable by everyone on the system.

**8. Run SQL for creating tables**

```bash

CREATE TABLE books (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT         NOT NULL,
    author      VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE token_blacklist (
    jti        VARCHAR(36)  NOT NULL PRIMARY KEY,
    expires_at BIGINT       NOT NULL
);

CREATE TABLE api_clients (
    client_id     VARCHAR(36)  NOT NULL PRIMARY KEY,
    client_secret VARCHAR(255) NOT NULL
);

```
---

## API Documentation

Swagger UI is available at:

```
http://localhost:8081
```

The OpenAPI spec is located at `openapi.yaml` in the root of this repository.

---

## Database GUI

phpMyAdmin is available at:

```
http://localhost:8000
```

Login with credentials from your `.env` file (`MYSQL_ROOT_PASSWORD`).

---

## Authentication Flow

Authentication is **version-neutral** (the token is the same across API versions), so it lives outside the version path:

```
POST /api/auth          ← get access + refresh tokens
POST /api/refresh       ← rotate refresh token
```

Include the access token in requests:

```
Authorization: Bearer <access_token>
```

---

## API Versioning

The API serves multiple versions. **Supported: `v1`, `v2`. Default: `v1`** (fixed — it will never silently jump to the newest version).

### Choosing a version

Three interchangeable strategies produce the same result:

**1. URL path** (recommended — explicit and cacheable)

```
GET /api/v1/books
GET /api/v2/books
```

The version in the path is authoritative; version headers are ignored here.

**2. `X-API-Version` header** (on the version-neutral path)

```
GET /api/books
X-API-Version: 2
```

**3. `Accept` content negotiation** (on the version-neutral path)

```
GET /api/books
Accept: application/vnd.api+json;version=2
```

### Resolution priority

On the neutral `/api/...` path, when several signals are present:

```
X-API-Version  →  Accept;version  →  default (v1)
```

- No version on the neutral path → **default v1**.
- Unknown version via header/`Accept` (e.g. `99`) → falls back to **default v1**.
- Unknown version in the URL (e.g. `/api/v99/books`) → **404 Not Found**.

### Deprecation (v1)

v1 is **deprecated** (sunset **2026-12-31**). Every v1 response carries RFC 8594 headers:

```
Deprecation: true
Sunset: Thu, 31 Dec 2026 00:00:00 GMT
Link: </docs/migration-v1-to-v2>; rel="deprecation"
```

The `Link` is host-relative, so it resolves to the migration guide on whatever host served the response.

### What changed in v2

v2 changes the **book response representation only** (request bodies are unchanged):

| Field (v1) | Field (v2) |
|---|---|
| `title` (string) | `name` (string) |
| `author` (string) | `author` (object: `{ "name": ... }`) |

### Migration guide

A human-readable guide is served by the API itself:

```
GET /docs/migration-v1-to-v2        → text/markdown
```

> The API versioning system was designed and implemented with [Claude Code](https://claude.com/claude-code).

---

## Rate Limit Headers

Every API response includes:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
```

When the limit is exceeded:

```
HTTP 429 Too Many Requests
```

---

## License

This project is open-source and available under the [MIT license](LICENSE).

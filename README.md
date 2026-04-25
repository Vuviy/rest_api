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
Docker configuration lives in a separate repository: [docker_for_rest_api](https://github.com/Vuviy/docker_for_rest_api) *(update link if needed)*

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
```

**4. Configure `.env`**

```env
DB_CONNECTION=mysql
DB_HOST=db_rest_api
DB_PORT=3306
DB_DATABASE=blog
DB_USERNAME=root
DB_PASSWORD=secret

MYSQL_ROOT_PASSWORD=secret
```

**5. Start Docker containers**

```bash
docker compose up -d
```

**6. Install dependencies**

```bash
docker compose exec rest_api_php composer install
```

**7. Generate RSA keys for JWT**

```bash
docker compose exec rest_api_php mkdir -p storage/keys

docker compose exec rest_api_php openssl genpkey -algorithm RSA \
  -out storage/keys/private.pem -pkeyopt rsa_keygen_bits:4096

docker compose exec rest_api_php openssl rsa -pubout \
  -in storage/keys/private.pem -out storage/keys/public.pem
```

Set correct permissions:

```bash
docker compose exec rest_api_php chmod 600 storage/keys/private.pem
docker compose exec rest_api_php chmod 644 storage/keys/public.pem
```

> If you get a permission error reading the keys inside the container, the issue is that the file owner differs from the PHP process user. Fix it with:
> ```bash
> docker compose exec rest_api_php chown www-data:www-data storage/keys/private.pem storage/keys/public.pem
> ```
> Avoid using `chmod 777` in production — it makes private keys readable by everyone on the system.

**8. Run migrations**

```bash
docker compose exec rest_api_php php migrations/migrate.php
```

**9. Open in browser**

```
http://localhost
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

```
POST /api/v1/auth/token          ← get access + refresh tokens
POST /api/v1/auth/refresh        ← rotate refresh token
POST /api/v1/auth/revoke         ← blacklist token (logout)
```

Include the access token in requests:

```
Authorization: Bearer <access_token>
```

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

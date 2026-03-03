<?php

declare(strict_types=1);

namespace App\Security\Middleware;

use App\Exception\AuthorizationException;
use App\Request;
use App\Response;
use App\Security\Repositories\BlacklistRepository;
use App\Security\Services\JwtService;

final class JwtMiddleware implements MiddlewareInterface
{
    public function __construct(
        private JwtService $jwt,
        private BlacklistRepository $blacklist
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        $header = $request->getHeader('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            throw new AuthorizationException('Missing or invalid Authorization header');
        }

        $token = substr($header, 7);

        $decoded = $this->jwt->decode($token);

        if ($decoded->type !== 'access') {
            throw new AuthorizationException('Invalid token type');
        }

        if ($this->blacklist->isBlacklisted($decoded->jti)) {
            throw new AuthorizationException('Token revoked');
        }

        $request->setAttribute('client_id', $decoded->client_id);
//        $request->setAttribute('client_id', 'ttttt');
        return $next($request);
    }
}

<?php

declare(strict_types=1);

namespace App\Security\Middleware;

use App\Exception\AuthorizationException;
use App\Request;
use App\Response;
use App\Security\Repositories\BlacklistRepository;
use App\Security\Services\JwtService;
use Exception;

final class TestMiddleware implements MiddlewareInterface
{

    public function handle(Request $request, callable $next): Response
    {
       dd('test middleware');

        return $next($request);
    }
}

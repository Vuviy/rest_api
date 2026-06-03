<?php

declare(strict_types=1);

namespace App\Versioning;

use App\Request;
use App\Response;
use App\Security\Middleware\MiddlewareInterface;

/**
 * Imperative shell around VersionResolver.
 * Resolves the API version for the neutral path and stores it on the request
 * so downstream middleware/controllers can read it via getAttribute('api_version').
 */
final class VersionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private VersionResolver $resolver,
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        $version = $this->resolver->resolve($request);
        $request->setAttribute('api_version', $version);

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }
}

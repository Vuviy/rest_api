<?php

declare(strict_types=1);

namespace App\Versioning;

use App\Request;
use App\Response;
use App\Security\Middleware\MiddlewareInterface;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Response-stage middleware: after the controller runs, if the resolved API version
 * is deprecated, it appends RFC 8594 Deprecation/Sunset headers plus a Link to the
 * migration guide. Centralized here so no controller has to know about deprecation.
 */
final class DeprecationMiddleware implements MiddlewareInterface
{
    /**
     * @param array<int, array{sunset: string, link: string}> $deprecated
     */
    public function __construct(
        private array $deprecated,
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $version = $request->getAttribute('api_version');
        if (!is_int($version) || !isset($this->deprecated[$version])) {
            return $response;
        }

        $info = $this->deprecated[$version];

        return $response
            ->withAddedHeader('Deprecation', 'true')
            ->withAddedHeader('Sunset', $this->toHttpDate($info['sunset']))
            ->withAddedHeader('Link', sprintf('<%s>; rel="deprecation"', $info['link']));
    }

    private function toHttpDate(string $isoDate): string
    {
        $date = new DateTimeImmutable($isoDate, new DateTimeZone('UTC'));

        return $date->format('D, d M Y H:i:s') . ' GMT';
    }
}

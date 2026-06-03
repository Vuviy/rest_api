<?php

declare(strict_types=1);

namespace App\Versioning;

use App\Request;

/**
 * Decides which API version a request targets on the version-neutral path.
 *
 * Functional core / imperative shell:
 *  - resolve() is the thin shell that extracts headers from the HTTP Request;
 *  - decide() is the pure decision logic (no HTTP dependency, trivially testable).
 *
 * Priority: URL path version → X-API-Version header → Accept content negotiation → default.
 * The URL version (/api/vN/...) is authoritative on versioned paths; headers apply only
 * on the neutral path. An unknown/unsupported version falls back to the default version.
 */
final class VersionResolver
{
    /**
     * @param list<int> $supportedVersions
     */
    public function __construct(
        private array $supportedVersions,
        private int $defaultVersion,
        private string $mediaType,
    ) {
    }

    public function resolve(Request $request): int
    {
        return $this->decide(
            $this->parseUrlVersion($request->getServerParam('REQUEST_URI')),
            $request->getHeader('X-API-Version'),
            $request->getHeader('Accept'),
        );
    }

    public function decide(?int $urlVersion, ?string $versionHeader, ?string $acceptHeader): int
    {
        // 0. URL path version (/api/vN/...) is authoritative when present; headers are ignored.
        if ($urlVersion !== null) {
            return $this->normalize($urlVersion);
        }

        // 1. Explicit version header wins over content negotiation (explicit beats implicit).
        $fromHeader = $this->parseVersionHeader($versionHeader);
        if ($fromHeader !== null) {
            return $this->normalize($fromHeader);
        }

        // 2. Accept content negotiation: application/vnd.api+json;version=N
        $fromAccept = $this->parseAcceptHeader($acceptHeader);
        if ($fromAccept !== null) {
            return $this->normalize($fromAccept);
        }

        // 3. Nothing provided → fixed default.
        return $this->defaultVersion;
    }

    private function parseUrlVersion(?string $uri): ?int
    {
        if ($uri === null) {
            return null;
        }

        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path)) {
            return null;
        }

        // Only a version segment right after /api (e.g. /api/v2/...) counts as a URL version.
        if (preg_match('#^/api/v(\d+)(?:/|$)#', $path, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    private function parseVersionHeader(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        // Only a bare positive integer counts as a version request; anything else is ignored.
        if ($value === '' || !ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }

    private function parseAcceptHeader(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (!str_contains($value, $this->mediaType)) {
            return null;
        }

        if (preg_match('/;\s*version=(\d+)/', $value, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    private function normalize(int $requested): int
    {
        return in_array($requested, $this->supportedVersions, true)
            ? $requested
            : $this->defaultVersion;
    }
}

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
 * Priority: X-API-Version header → Accept content negotiation → default.
 * An unknown/unsupported version falls back to the default version.
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
            $request->getHeader('X-API-Version'),
            $request->getHeader('Accept'),
        );
    }

    public function decide(?string $versionHeader, ?string $acceptHeader): int
    {
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

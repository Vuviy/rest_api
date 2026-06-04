<?php

declare(strict_types=1);

/*
 * Single source of truth for API versioning policy.
 * Pure data — no logic. Consumed by VersionResolver and the version/deprecation middleware.
 */

return [
    // Every version the API is able to serve.
    'supported' => [1, 2],

    // Fixed default version: used on the neutral path when no version is given,
    // and as the fallback for an unknown/unsupported requested version.
    // Kept fixed (not "latest") so a future major release never silently migrates clients.
    'default' => 1,

    // Base media type for Accept content negotiation: application/vnd.api+json;version=N
    'media_type' => 'application/vnd.api+json',

    // Deprecated versions → metadata for the Deprecation/Sunset response headers (RFC 8594).
    // 'sunset' is an ISO date here; the deprecation middleware formats it to an HTTP-date.
    'deprecated' => [
        1 => [
            'sunset' => '2026-12-31',
            // Host-relative URI on purpose: an RFC 8288 Link target may be relative and is
            // resolved by the client against the request URL, so it always points to the
            // host the API is actually served from (localhost in dev, the real domain in
            // prod) without hardcoding an environment-specific host here.
            'link'   => '/docs/migration-v1-to-v2',
        ],
    ],
];

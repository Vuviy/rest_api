<?php

declare(strict_types=1);

namespace App\Versioning\Transformers;

/**
 * Picks the output transformer for a resolved API version.
 * Adding v3 = one more `match` arm; controllers and services stay untouched.
 */
final class BookTransformerFactory
{
    public function for(int $version): BookTransformer
    {
        return match ($version) {
            2 => new BookTransformerV2(),
            default => new BookTransformerV1(),
        };
    }
}

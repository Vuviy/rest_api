<?php

declare(strict_types=1);

namespace App\Versioning\Transformers;

/**
 * v1 output shape — the original representation, kept unchanged for backward compatibility.
 */
final class BookTransformerV1 implements BookTransformer
{
    public function transform(array $book): array
    {
        // Identity: v1 clients keep receiving exactly {id, title, author, description}.
        return $book;
    }
}

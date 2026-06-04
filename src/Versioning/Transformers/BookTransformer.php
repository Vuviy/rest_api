<?php

declare(strict_types=1);

namespace App\Versioning\Transformers;

/**
 * Shapes a single book's array representation for a specific API version.
 * Business logic stays in the service; only the OUTPUT shape differs per version.
 *
 * @phpstan-type BookRow array{id: int|null, title: string, author: string, description: string}
 */
interface BookTransformer
{
    /**
     * @param array<string, mixed> $book the book's array form ({id, title, author, description})
     * @return array<string, mixed>
     */
    public function transform(array $book): array;
}

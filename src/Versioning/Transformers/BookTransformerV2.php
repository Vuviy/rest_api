<?php

declare(strict_types=1);

namespace App\Versioning\Transformers;

/**
 * v2 output shape — demonstrates breaking changes done safely behind versioning:
 *  - `title` is renamed to `name`
 *  - `author` becomes a nested object instead of a flat string
 */
final class BookTransformerV2 implements BookTransformer
{
    public function transform(array $book): array
    {
        return [
            'id'          => $book['id'],
            'name'        => $book['title'],
            'author'      => ['name' => $book['author']],
            'description' => $book['description'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\DTO;

final class ListBooksDTO
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 10,
        public ?string $sort = null,
        public string $orderBy = 'asc',
        public ?string $author = null,
        public ?string $title = null,
    ) {
    }
}

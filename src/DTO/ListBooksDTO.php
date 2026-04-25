<?php

declare(strict_types=1);

namespace App\DTO;

use App\Attributes\Max;
use App\Attributes\Min;
use App\Attributes\Orderable;
use App\Attributes\Sortable;

final class ListBooksDTO
{
    public function __construct(
        #[Min(1)]
        public int $page = 1,
        #[Min(1)]
        #[Max(100)]
        public int $perPage = 10,
        #[Sortable(['title', 'author', 'created_at'])]
        public ?string $sort = null,
        #[Orderable()]
        public string $orderBy = 'asc',
        public ?string $author = null,
        public ?string $title = null,
        public ?int $cursor = null,
    ) {
    }
}

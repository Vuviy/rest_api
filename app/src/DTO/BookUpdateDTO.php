<?php

declare(strict_types=1);

namespace App\DTO;

use App\Attributes\NotEmpty;
use App\Attributes\Required;

final class BookUpdateDTO
{
    public function __construct(
        public string $id,
        #[Required()]
        #[NotEmpty()]
        public ?string $title,
        #[Required()]
        #[NotEmpty()]
        public ?string $author,
        #[Required()]
        #[NotEmpty()]
        public ?string $description,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\DTO;

use App\Attributes\NotEmpty;
use App\Attributes\Required;
use ReflectionClass;

final class BookPatchDTO
{
    public function __construct(
        #[NotEmpty()]
        public ?string $title,
        #[NotEmpty()]
        public ?string $author,
        #[NotEmpty()]
        public ?string $description,
    ) {
    }

    public function toArray(): array
    {
        $ref = new ReflectionClass($this);

        $data = [];

        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);

            $name = $property->getName();
            $value = $property->getValue($this);
            if (null !== $value) {
                $data[$name] = $value;
            }
        }

        return $data;
    }
}

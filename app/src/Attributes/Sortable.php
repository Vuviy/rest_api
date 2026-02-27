<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Sortable implements ValidationAttribute
{
    public function __construct(private array $allowedSorts = ['id'])
    {
    }

    public function validate(string $propertyName, mixed $value): ?string
    {
        if (null !== $value && !in_array($value, $this->allowedSorts, true)) {
            return 'Invalid sort field';
        }
        return null;
    }
}

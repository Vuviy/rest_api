<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Orderable implements ValidationAttribute
{
    public function __construct(private array $allowedOrderBy = ['asc', 'desc'])
    {
    }

    public function validate(string $propertyName, mixed $value): ?string
    {
        if (null !== $value && !in_array($value, $this->allowedOrderBy, true)) {
            return 'Direction must be asc or desc';
        }
        return null;
    }
}

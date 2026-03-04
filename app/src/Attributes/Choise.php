<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Choise implements ValidationAttribute
{
    public function __construct(private array $allowedSorts)
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

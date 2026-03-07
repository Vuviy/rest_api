<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Choice implements ValidationAttribute
{
    public function __construct(private array $allowed)
    {
    }

    public function validate(string $propertyName, mixed $value): ?string
    {
        if (null !== $value && !in_array($value, $this->allowed, true)) {
            return 'Invalid sort field';
        }
        return null;
    }
}

<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Min implements ValidationAttribute
{
    public function __construct(private int $min)
    {
    }

    public function validate(string $propertyName, mixed $value): ?string
    {
        if ($value < $this->min) {
            return "{$propertyName} must be >= {$this->min}";
        }
        return null;
    }
}

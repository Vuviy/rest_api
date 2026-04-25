<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Max implements ValidationAttribute
{
    public function __construct(private int $max)
    {
    }

    public function validate(string $property, mixed $value): ?string
    {
        if ($value > $this->max) {
            return "{$property} must be <= {$this->max}";
        }
        return null;
    }
}

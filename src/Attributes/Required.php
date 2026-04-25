<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Required implements ValidationAttribute
{
    public function validate(string $propertyName, mixed $value): ?string
    {
        if (null === $value) {
            return sprintf('Field %s is required', $propertyName);
        }
        return null;
    }
}

<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotEmpty implements ValidationAttribute
{
    public function validate(string $propertyName, mixed $value): ?string
    {
        if (null !== $value) {
            if ('' === trim($value)) {
                return sprintf('Field %s cannot be empty', $propertyName);
            }
        }
        return null;
    }
}

<?php

namespace App\Attributes;

interface ValidationAttribute
{
    public function validate(string $propertyName, mixed $value): ?string;
}

<?php

namespace App\Validators;

use App\DTO\ListBooksDTO;
use App\Exception\ValidationException;
use ReflectionClass;

final class AttributeValidator
{
    public static function validate(object $dto): void
    {
        $ref = new ReflectionClass($dto);
        $errors = [];
        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($dto);

            foreach ($property->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();

                $error = $instance->validate($property->getName(), $value);

                if ($error) {
                    $errors[] = $error;
                }
            }
        }

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
    }
}

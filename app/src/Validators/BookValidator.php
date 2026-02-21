<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exception\ValidationException;

class BookValidator
{
    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    public function validateCreate(array $data): void
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        }

        if (empty($data['author'])) {
            $errors['author'] = 'Author is required';
        }

        if (!array_key_exists('description', $data) || '' === $data['description']) {
            $errors['description'] = 'Description is required';
        }

        if ($errors) {
            throw new ValidationException($errors);
        }
    }

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    public function validatePatch(array $data): void
    {
        $errors = [];

        if (array_key_exists('description', $data) && '' === $data['description']) {
            $errors['description'] = 'Description is required';
        }

        if (array_key_exists('author', $data) && '' === $data['author']) {
            $errors['author'] = 'Author is required';
        }

        if (array_key_exists('title', $data) && '' === $data['title']) {
            $errors['title'] = 'Title is required';
        }

        if ($errors) {
            throw new ValidationException($errors);
        }
    }
}

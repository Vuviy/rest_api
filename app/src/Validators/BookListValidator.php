<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exception\ValidationException;

class BookListValidator
{
    private array $allowedSorts = ['title', 'author', 'created_at'];

    public function validate(array $query): void
    {
        $errors = [];

        if (array_key_exists('page', $query) && (int)$query['page'] < 1) {
            $errors['page'] = 'Page must be >= 1';
        }

        if (array_key_exists('per_page', $query) && ((int)$query['per_page'] < 1 || (int)$query['per_page'] > 100)) {
            $errors['perPage'] = 'per_page must be between 1 and 100';
        }

        if (array_key_exists('sort', $query) && !in_array($query['sort'], $this->allowedSorts, true)) {
            $errors['sort'] = 'Invalid sort field';
        }

        if (array_key_exists('orderBy', $query) && !in_array(strtolower($query['orderBy']), ['asc', 'desc'], true)) {
            $errors['orderBy'] = 'Direction must be asc or desc';
        }

        if ($errors) {
            throw new ValidationException($errors);
        }
    }
}

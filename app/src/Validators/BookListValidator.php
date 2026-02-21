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

        if (isset($query['page']) && (int)$query['page'] < 1) {
            $errors['page'] = 'Page must be >= 1';
        }

        if (isset($query['perPage']) && ((int)$query['perPage'] < 1 || (int)$query['perPage'] > 100)) {
            $errors['perPage'] = 'per_page must be between 1 and 100';
        }

        if (isset($query['sort']) && !in_array($query['sort'], $this->allowedSorts, true)) {
            $errors['sort'] = 'Invalid sort field';
        }

        if (isset($query['orderBy']) && !in_array(strtolower($query['orderBy']), ['asc', 'desc'], true)) {
            $errors['orderBy'] = 'Direction must be asc or desc';
        }

        if ($errors) {
            throw new ValidationException($errors);
        }
    }
}

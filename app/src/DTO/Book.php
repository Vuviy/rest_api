<?php

declare(strict_types=1);

namespace App\DTO;

final class Book
{
    public function __construct(
        public ?int $id = null,
        public string $title,
        public string $author,
        public string $description,
    ) {
    }

    public function toArray(bool $links = true): array
    {
        $arr =  [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
        ];

        if ($links) {
            $arr['links'] = $this->getLinks();
        }

        return $arr;
    }

    private function getLinks(): array
    {
        return [
            'self' => sprintf('/books/%d', $this->id),
            'update' => sprintf('/books/%d', $this->id),
            'delete' => sprintf('/books/%d', $this->id),
            'author' => sprintf('/authors/%s', urlencode($this->author))
        ];
    }
}

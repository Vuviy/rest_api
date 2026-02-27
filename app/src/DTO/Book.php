<?php

declare(strict_types=1);

namespace App\DTO;

use App\Attributes\NotEmpty;
use App\Attributes\Required;

final class Book implements \JsonSerializable
{
    public function __construct(
        public ?int $id = null,
        #[Required()]
        #[NotEmpty()]
        public string $title,
        #[Required()]
        #[NotEmpty()]
        public string $author,
        #[Required()]
        #[NotEmpty()]
        public string $description,
    ) {
    }

    public function toArray(): array
    {
        $arr =  [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
        ];

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

    public function jsonSerialize(): mixed
    {
        $data = $this->toArray();

        $data['links'] = $this->getLinks();

        return $data;
    }

    static function fromArray(array $data): Book
    {
        return new Book(
            id: $data['id'],
            title: $data['title'],
            author: $data['author'],
            description: $data['description']
        );
    }
}

<?php

namespace App\Service;

use App\DTO\Book;
use App\DTO\ListBooksDTO;
use App\Exception\HttpException;
use App\Repositories\BookRepository;
use Exception;

final class BookService
{
    public function __construct(private BookRepository $repository)
    {
    }

    public function list(ListBooksDTO $dto): array
    {
        $offset = ($dto->page - 1) * $dto->perPage;

        $books = $this->repository->getAll(
            filters: [
                'author' => $dto->author,
                'title' => $dto->title
            ],
            sort: $dto->sort,
            orderBy: $dto->orderBy,
            limit: $dto->perPage,
            offset: $offset
        );

        $total = $this->repository->count([
            'author' => $dto->author,
            'title' => $dto->title
        ]);

        $links = $this->buildPaginationLinks($dto, $total, (array) $dto);

        return [
            'data' => $books,
            'headers' => [
                'Link' => $links
            ]
        ];
    }


    private function buildPaginationLinks(ListBooksDTO $dto, int $total, array $queryParams): string
    {
        $links = [];
        $totalPages = (int) ceil($total / $dto->perPage);

        $baseParams = $queryParams;
        unset($baseParams['page']);

        $buildUrl = function (int $page) use ($baseParams) {
            $params = array_merge($baseParams, ['page' => $page]);
            $queryString = http_build_query($params);

            return sprintf('/books?%s', $queryString);
        };

        if ($dto->page < $totalPages) {
            $links[] = sprintf('<%s>; rel="next"', $buildUrl($dto->page + 1));
        }

        if ($dto->page > 1) {
            $links[] = sprintf('<%s>; rel="prev"', $buildUrl($dto->page - 1));
        }

        if ($dto->page !== 1) {
            $links[] = sprintf('<%s>; rel="first"', $buildUrl(1));
        }

        if ($dto->page !== $totalPages && $totalPages > 0) {
            $links[] = sprintf('<%s>; rel="last"', $buildUrl($totalPages));
        }

        return implode(', ', $links);
    }

    public function getById(string $id): array
    {
        $book =  $this->repository->getById($id);

        if (!$book) {
            throw new HttpException('Book not found', 404);
        }

        return $book;
    }

    public function create(array $data): int
    {
        $book = new Book(
            id: null,
            title: $data['title'],
            author: $data['author'],
            description: $data['description']
        );

        return $this->repository->save($book);
    }

    public function replace(string $id, array $data): int
    {
        $book = new Book(
            id: (int)$id,
            title: $data['title'],
            author: $data['author'],
            description: $data['description']
        );

        return $this->repository->update($book);
    }

    public function updateFields(string $id, array $data): int
    {
        return $this->repository->updatePartial($id, $data);
    }

    public function delete(string $id): void
    {
        $this->repository->delete((int)$id);
    }
}

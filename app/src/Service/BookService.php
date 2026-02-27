<?php

namespace App\Service;

use App\DTO\Book;
use App\DTO\BookCreateDTO;
use App\DTO\BookPatchDTO;
use App\DTO\BookUpdateDTO;
use App\DTO\ListBooksDTO;
use App\Exception\HttpException;
use App\Exception\NotFoundException;
use App\Repositories\BookRepository;
use Exception;

final class BookService
{
    public function __construct(private BookRepository $repository)
    {
    }

    public function list(ListBooksDTO $dto): array
    {
        if ($dto->cursor !== null) {
            return $this->listWithCursor($dto);
        }

        return $this->listWithOffset($dto);
    }

    private function listWithCursor(ListBooksDTO $dto): array
    {

        $books = $this->repository->getAllWithCursor(
            filters: [
                'author' => $dto->author,
                'title' => $dto->title
            ],
            sort: $dto->sort ?? 'id',
            orderBy: $dto->orderBy ?? 'asc',
            limit: $dto->perPage,
            cursor: $dto->cursor
        );

        $total = $this->repository->count([
            'author' => $dto->author,
            'title' => $dto->title
        ]);

        $links = $this->buildPaginationLinks($dto, $total, (array)$dto);


        return [
            'data' => $books,
            'headers' => [
                'link' => $links
            ]
        ];
    }


    public function listWithOffset(ListBooksDTO $dto): array
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

        $links = $this->buildPaginationLinks($dto, $total, (array)$dto);

        return [
            'data' => $books,
            'headers' => [
                'link' => $links
            ]
        ];
    }


    private function buildPaginationLinks(ListBooksDTO $dto, int $total, array $queryParams): string
    {
        $links = [];
        $totalPages = (int)ceil($total / $dto->perPage);

        $baseParams = $queryParams;

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
        $book = $this->repository->getById($id);

        if (!$book) {
            throw new NotFoundException(sprintf('Book with id %s not found', $id));
        }

        return $book;
    }

    public function create(BookCreateDTO $dto): int
    {
        $book = new Book(
            id: null,
            title: $dto->title,
            author: $dto->author,
            description: $dto->description
        );
        return $this->repository->save($book);
    }

    public function replace(BookUpdateDTO $dto, string $id): int
    {
        $book = new Book(
            id: (int)$id,
            title: $dto->title,
            author: $dto->author,
            description: $dto->description
        );

        return $this->repository->update($book);
    }

    public function updateFields(string $id, BookPatchDTO $dto): int
    {
        return $this->repository->updatePartial($id, $dto->toArray());
    }

    public function delete(string $id): void
    {
        $this->repository->delete((int)$id);
    }
}

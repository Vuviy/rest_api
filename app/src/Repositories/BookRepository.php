<?php

namespace App\Repositories;

use App\Database\Database;
use App\DTO\Book;
use InvalidArgumentException;

final class BookRepository
{
    private const TABLE_NAME = 'books';
    private const FILTERED_COLS = ['title', 'author', 'description'];
    private $columns = ['id', 'title', 'author', 'description', 'created_at'];

    public function __construct(private Database $db)
    {
    }

    public function getAll(
        array $filters,
        ?string $sort,
        string $orderBy,
        int $limit,
        int $offset,
    ): array {

        $query = $this->db->table(self::TABLE_NAME);

        foreach ($filters as $field => $value) {
            if (
                in_array($field, $this->columns, true) &&
                $value !== null &&
                $value !== ''
            ) {
                if (in_array($field, self::FILTERED_COLS, true)) {
                    $query->where($field, 'LIKE', "%{$value}%");
                } else {
                    $query->where($field, '=', $value);
                }
            }
        }

        if ($sort !== null && in_array($sort, $this->columns, true)) {
            $orderBy = strtolower($orderBy) === 'desc' ? 'desc' : 'asc';

            $query->orderBy($sort, $orderBy);
        }

        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $query->limit($limit);
        $query->offset($offset);

        $booksArr = $query->get();

        $books = [];

        foreach ($booksArr as $book) {
            $bookObj = new Book(
                $book['id'],
                $book['title'],
                $book['author'],
                $book['description']
            );
            $books[] = $bookObj->toArray();
        }

        return $books;
    }

    public function getAllWithCursor(
        array $filters,
        string $sort,
        string $orderBy,
        int $limit,
        string $cursor
    ): array {
        $query = $this->db->table(self::TABLE_NAME);

        foreach ($filters as $field => $value) {
            if (
                in_array($field, $this->columns, true) &&
                $value !== null &&
                $value !== ''
            ) {
                if (in_array($field, self::FILTERED_COLS, true)) {
                    $query->where($field, 'LIKE', "%{$value}%");
                } else {
                    $query->where($field, '=', $value);
                }
            }
        }

        $query->cursor('id', $cursor, $orderBy);
        $query->limit($limit);

        $booksArr = $query->get();
        $books = [];

        foreach ($booksArr as $book) {
            $bookObj = Book::fromArray($book);
            $books[] = $bookObj;
        }

        return $books;
    }


    public function count(array $filters): int
    {
        $query = $this->db->table(self::TABLE_NAME);

        foreach ($filters as $field => $value) {
            if (
                in_array($field, $this->columns, true) &&
                $value !== null &&
                $value !== ''
            ) {
                if (in_array($field, self::FILTERED_COLS, true)) {
                    $query->where($field, 'LIKE', "%{$value}%");
                } else {
                    $query->where($field, '=', $value);
                }
            }
        }

        return $query->count();
    }

    public function getById(string $id): Book
    {
        $book = $this->db->table(self::TABLE_NAME)->where('id', '=', $id)->first();

        $book = Book::fromArray($book);

        return $book;
    }

    public function save(Book $book): int
    {
        return $this->db->table(self::TABLE_NAME)->insert($book->toArray());
    }

    public function update(Book $book): int
    {
        return $this->db->table(self::TABLE_NAME)->where('id', '=', $book->id)->update($book->toArray());
    }

    public function updatePartial(string $id, array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        return $this->db
            ->table(self::TABLE_NAME)
            ->where('id', '=', $id)
            ->update($data);
    }

    public function delete(int $id): void
    {
        $this->db->table(self::TABLE_NAME)->where('id', '=', $id)->delete();
    }
}

<?php

namespace App\Repositories;

use App\Database\Database;
use App\DTO\Book;

final class BookRepository
{
    private $columns = ['id', 'title', 'author', 'description', 'created_at'];

    public function __construct(private Database $db)
    {
    }

    public function getAll(
        array $filters,
        ?string $sort,
        string $orderBy,
        int $limit,
        int $offset
    ): array {

        $query = $this->db->table('books');

        foreach ($filters as $field => $value) {
            if (
                in_array($field, $this->columns, true) &&
                $value !== null &&
                $value !== ''
            ) {
                if (in_array($field, ['title', 'author', 'description'], true)) {
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


    public function count(array $filters): int
    {
        $query = $this->db->table('books');

        foreach ($filters as $field => $value) {
            if (
                in_array($field, $this->columns, true) &&
                $value !== null &&
                $value !== ''
            ) {
                if (in_array($field, ['title', 'author', 'description'], true)) {
                    $query->where($field, 'LIKE', "%{$value}%");
                } else {
                    $query->where($field, '=', $value);
                }
            }
        }

        return $query->count();
    }

    public function getById(string $id): array
    {
        $book = $this->db->table('books')->where('id', '=', $id)->first();
        $book = new Book($book['id'], $book['title'], $book['author'], $book['description']);

        return $book->toArray();
    }

    public function save(Book $book): int
    {
        return $this->db->table('books')->insert($book->toArray(false));
    }

    public function update(Book $book): int
    {
        return $this->db->table('books')->where('id', '=', $book->id)->update($book->toArray(false));
    }

    public function updatePartial(string $id, array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        return $this->db
            ->table('books')
            ->where('id', '=', $id)
            ->update($data);
    }

    public function delete(int $id): void
    {
        $this->db->table('books')->where('id', '=', $id)->delete();
    }
}

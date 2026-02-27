<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Book;
use App\DTO\BookCreateDTO;
use App\DTO\BookPatchDTO;
use App\DTO\BookUpdateDTO;
use App\DTO\ListBooksDTO;
use App\Enums\HttpStatus;
use App\Exception\ValidationException;
use App\Request;
use App\Response;
use App\Service\BookService;
use App\Validators\AttributeValidator;
use App\Validators\BookListValidator;
use App\Validators\BookValidator;
use Exception;
use ReflectionClass;

final class BookController
{
    public function __construct(
        private BookService $service,
        private BookValidator $validator,
        //        private BookListValidator $listValidator,
        private AttributeValidator $attributeValidator,
    ) {
    }

    public function list(Request $request): Response
    {
        $data = $request->get();

        $dto = new ListBooksDTO(
            page: array_key_exists('page', $data) ? (int)$data['page'] : 1,
            perPage: array_key_exists('perPage', $data) ? (int)$data['perPage'] : 10,
            sort: $data['sort'] ?? null,
            orderBy: $data['orderBy'] ?? 'asc',
            author: $data['author'] ?? null,
            title: $data['title'] ?? null,
            cursor: array_key_exists('cursor', $data) ? (int)$data['cursor'] : null,
        );

        $this->attributeValidator->validate($dto);

        $result = $this->service->list($dto);

        return new Response(
            $result['data'],
            HttpStatus::OK,
            $result['headers']
        );
    }

    public function getById(Request $request, string $id): Response
    {
        $book = $this->service->getById($id);

        return new Response($book, HttpStatus::OK);
    }

    public function store(Request $request): Response
    {
        $data = $request->post();

        $dto = new BookCreateDTO(
            title: $data['title'] ?? null,
            author: $data['author'] ?? null,
            description: $data['description'] ?? null,
        );
        $this->attributeValidator->validate($dto);

        $id = $this->service->create($dto);

        return new Response(
            ['id' => $id],
            HttpStatus::CREATED,
            [
                'Location' => sprintf('/books/%s', $id)
            ]
        );
    }

    public function update(Request $request, string $id): Response
    {
        $data = $request->getJson();

        $dto = new BookUpdateDTO(
            title: $data['title'] ?? null,
            author: $data['author'] ?? null,
            description: $data['description'] ?? null,
        );

        $this->attributeValidator->validate($dto);

        $this->service->replace($dto, $id);

        return new Response(['id' => $id], HttpStatus::ACCEPTED);
    }

    public function patch(Request $request, string $id): Response
    {
        $data = $request->getJson();

        $dto = new BookPatchDTO(
            title: $data['title'] ?? null,
            author: $data['author'] ?? null,
            description: $data['description'] ?? null,
        );

        $this->attributeValidator->validate($dto);

        $this->service->updateFields($id, $dto);

        return new Response(
            ['id' => $id],
            HttpStatus::ACCEPTED
        );
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->service->delete($id);

        return new Response('', HttpStatus::NO_CONTENT);
    }
}

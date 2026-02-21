<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ListBooksDTO;
use App\Request;
use App\Response;
use App\Service\BookService;
use App\Validators\BookListValidator;
use App\Validators\BookValidator;

final class BookController
{
    public function __construct(
        private BookService $service,
        private BookValidator $validator,
        private BookListValidator $listValidator,
    ) {
    }

    public function index(Request $request): Response
    {
        $data = $request->get();
        $this->listValidator->validate($request->get());

        $dto = new ListBooksDTO(
            page: isset($data['page']) ? (int)$data['page'] : 1,
            perPage: isset($data['per_page']) ? (int)$data['per_page'] : 10,
            sort: $data['sort'] ?? null,
            orderBy: $data['orderBy'] ?? 'asc',
            author: $data['author'] ?? null,
            title: $data['title'] ?? null,
        );

        $result = $this->service->list($dto);

        return new Response(
            $result['data'],
            200,
            $result['headers']
        );
    }

    public function getById(Request $request, string $id): Response
    {
        $book = $this->service->getById($id);

        return new Response($book);
    }

    public function store(Request $request): Response
    {
        $data = $request->post();

        $this->validator->validateCreate($data);

        $id = $this->service->create($data);

        return new Response(
            ['id' => $id],
            201,
            [
                'Location' =>  sprintf('/books/%s', $id)
            ]
        );
    }

    public function update(Request $request, string $id): Response
    {
        $data = $request->getJson();

        $this->validator->validateCreate($data);

        $this->service->replace($id, $data);

        return new Response(['id' => $id], 200);
    }

    public function patch(Request $request, string $id): Response
    {
        $dataJson = $request->getJson();

        $this->validator->validatePatch($dataJson);

        $allowed = ['title','author','description'];

        $data = array_intersect_key($dataJson, array_flip($allowed));

        $this->service->updateFields($id, $data);

        return new Response(
            ['id' => $id],
            200
        );
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->service->delete($id);

        return new Response('', 204);
    }
}

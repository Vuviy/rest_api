<?php

namespace App\Security\Repositories;

use App\Database\Database;
use App\Exception\NotFoundException;
use App\Security\DTO\ClientApi;

final class ClientsApiRepository
{
    private const TABLE_NAME = 'api_clients';

    public function __construct(private Database $db)
    {
    }

    public function add(string $client_id, int $client_secret): void
    {
        $data = [
            'client_id' => $client_id,
            'client_secret' => $client_secret
        ];
        $this->db->table(self::TABLE_NAME)->insert($data);
    }

    public function getAll(): array
    {
        return $this->db->table(self::TABLE_NAME)->get();
    }

    public function findByClientId(int $id): ClientApi
    {
        $data = $this->db->table(self::TABLE_NAME)->where('client_id', '=', $id)->get();

        if (false === $data) {
            throw new NotFoundException('Client not found');
        }

        return new ClientApi($data['client_id'], $data['client_secret']);
    }
}

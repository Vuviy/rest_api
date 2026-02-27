<?php

declare(strict_types=1);

namespace App\Security\Repositories;

use App\Database\Database;

final class BlacklistRepository
{
    private const TABLE_NAME = 'token_blacklist';

    public function __construct(private Database $db)
    {
    }

    public function add(string $jti, int $exp): void
    {
        $data = [
            'jti' => $jti,
            'expires_at' => $exp
        ];
        $this->db->table(self::TABLE_NAME)->insert($data);
    }

    public function isBlacklisted(string $jti): bool
    {
        return (bool)$this->db->table(self::TABLE_NAME)->where('jti', '=', $jti)->first();
    }

    public function cleanupExpired(): void
    {
        $this->db->table(self::TABLE_NAME)->where('exp', '<', time())->delete();
    }
}

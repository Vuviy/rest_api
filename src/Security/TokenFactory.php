<?php

declare(strict_types=1);

namespace App\Security;

final class TokenFactory
{
    public function createAccessPayloadForClient(string $clientId): array
    {
        $now = time();
        return [
            'sub' => $clientId,
            'type' => 'access',
            'jti' => bin2hex(random_bytes(16)),
            'iat' => $now,
            'exp' => $now + 900,
            'client_id' => $clientId
        ];
    }

    public function createRefreshPayloadForClient(string $clientId): array
    {
        $now = time();
        return [
            'sub' => $clientId,
            'type' => 'refresh',
            'jti' => bin2hex(random_bytes(16)),
            'iat' => $now,
            'exp' => $now + 604800,
            'client_id' => $clientId
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Security\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JwtService
{
    private string $privateKey;
    private string $publicKey;

    public function __construct(
        string $privateKeyPath,
        string $publicKeyPath
    ) {
        $this->privateKey = file_get_contents($privateKeyPath);
        $this->publicKey = file_get_contents($publicKeyPath);

        if (!$this->privateKey || !$this->publicKey) {
            throw new \RuntimeException('JWT keys not found');
        }
    }

    public function encode(array $payload): string
    {
        return JWT::encode(
            $payload,
            $this->privateKey,
            'RS256'
        );
    }

    public function decode(string $token): object
    {
        return JWT::decode(
            $token,
            new Key($this->publicKey, 'RS256')
        );
    }
}

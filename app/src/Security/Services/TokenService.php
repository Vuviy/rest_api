<?php

declare(strict_types=1);

namespace App\Security\Services;

use App\Exception\AuthorizationException;
use App\Security\Repositories\BlacklistRepository;
use App\Security\Services\JwtService;
use App\Security\TokenFactory;
use Exception;

final class TokenService
{
    public function __construct(
        private JwtService $jwt,
        private TokenFactory $factory,
        private BlacklistRepository $blacklist
    ) {
    }

    public function issueTokensForClient(string $clientId): array
    {
        $accessPayload = $this->factory->createAccessPayloadForClient($clientId);
        $refreshPayload = $this->factory->createRefreshPayloadForClient($clientId);

        $accessToken = $this->jwt->encode($accessPayload);
        $refreshToken = $this->jwt->encode($refreshPayload);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    public function refresh(string $refreshToken): array
    {
        $decoded = $this->jwt->decode($refreshToken);
        if ($decoded->type !== 'refresh') {
            throw new AuthorizationException('Invalid token type');
        }

        if ($this->blacklist->isBlacklisted($decoded->jti)) {
            throw new AuthorizationException('Token already revoked');
        }

        $this->blacklist->add($decoded->jti, $decoded->exp);

        return $this->issueTokensForClient($decoded->client_id);
    }

    public function revoke(string $token): void
    {
        $decoded = $this->jwt->decode($token);

        $this->blacklist->add($decoded->jti, $decoded->exp);
    }
}

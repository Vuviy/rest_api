<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Enums\HttpStatus;
use App\Exception\AuthorizationException;
use App\Request;
use App\Response;
use App\Security\Repositories\ClientsApiRepository;
use App\Security\Services\TokenService;

final class AuthController
{
    public function __construct(
        private TokenService $tokens,
        private ClientsApiRepository $clientsApiRepository
    ) {
    }

    public function auth(Request $request): Response
    {
        $data = $request->getJson();

        $clientId = $data['client_id'] ?? null;
        $clientSecret = $data['client_secret'] ?? null;

        if (!$clientId || !$clientSecret) {
            throw new AuthorizationException('Invalid credentials');
        }

        $clientApi = $this->clientsApiRepository->findByClientId($clientId);

        if (!$clientApi || !hash_equals($clientApi->client_secret, $clientSecret)) {
            throw new AuthorizationException('Invalid credentials');
        }

        $tokens = $this->tokens->issueTokensForClient($clientId);

        return new Response([
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'client_id' => $clientId,
            'issued' => time(),
            'expires' => time() + 900
        ], HttpStatus::OK);
    }

    public function refresh(Request $request): Response
    {
        $data = $request->getJson();

        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            throw new AuthorizationException('Refresh token required');
        }

        $tokens = $this->tokens->refresh($refreshToken);

        return new Response($tokens, HttpStatus::OK);
    }
}

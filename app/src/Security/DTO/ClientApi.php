<?php

declare(strict_types=1);

namespace App\Security\DTO;

final class ClientApi
{
    public function __construct(
        public string $client_id,
        public string $client_secret,
    ) {
    }
}

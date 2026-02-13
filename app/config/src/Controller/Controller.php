<?php

namespace App\Controller;

use App\Database\Database;
use App\DTO\Session;
use App\Repository\SessionRepository;
use App\SessionCrypto;
use App\SessionFingerprint;
use App\SessionManager;
use DateTime;

final class Controller
{
    public function test()
    {

        $crypto = new SessionCrypto(cypherKey());
        $repo = new SessionRepository(new Database(config()));
        $fingerprint = new SessionFingerprint();
        $session = new SessionManager($repo, $fingerprint, $crypto);

        echo 'home';
    }
}

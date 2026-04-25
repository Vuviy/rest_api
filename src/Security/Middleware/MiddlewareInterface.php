<?php

namespace App\Security\Middleware;

use App\Request;
use App\Response;
use Psr\Http\Message\RequestInterface;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;

}
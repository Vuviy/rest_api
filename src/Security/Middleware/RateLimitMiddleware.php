<?php

namespace App\Security\Middleware;

use App\Exception\TooManyRequestsException;
use App\Redis\RedisRateLimiter;
use App\Request;
use App\Response;

class RateLimitMiddleware  implements MiddlewareInterface
{
    public function __construct(
        private RedisRateLimiter $limiter,
        private array $config
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        $method = $request->getMethod();

        if (!array_key_exists($method, $this->config)) {
            return $next($request);
        }

        $limitConfig = $this->config[$method];

        $this->limiter->setCapacity($limitConfig['capacity']);
        $this->limiter->setRefillRate($limitConfig['refillRate']);

        $keys = $this->buildKeys($request, $method);


        foreach ($keys as $key) {
            if (!$this->limiter->consume($key)) {

                $remaining = $this->limiter->getAvailableTokens($key);

                throw new TooManyRequestsException(
                    $limitConfig['capacity'],
                    $remaining
                );
            }
        }

        $response = $next($request);

        $remaining = $this->limiter->getAvailableTokens($keys[0]);

        return $response
            ->withAddedHeader('X-RateLimit-Limit', (string)$limitConfig['capacity'])
            ->withAddedHeader('X-RateLimit-Remaining', (string)$remaining);
    }

    private function buildKeys(Request $request, string $method): array
    {
        $ip = $request->getServerParam('REMOTE_ADDR');
        $clientId = $request->getAttribute('client_id');

        $keys = ["ip:$method:$ip"];

        if ($clientId !== null) {
            $keys[] = "user:$method:$clientId";
        }

        return $keys;
    }
}
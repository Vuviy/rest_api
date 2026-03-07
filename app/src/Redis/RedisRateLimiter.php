<?php

namespace App\Redis;


use App\RateLimiterInterface;
use Redis;
use RuntimeException;
use function Amp\now;

final class RedisRateLimiter implements RateLimiterInterface
{
    private string $prefix = 'rate_limit:';
    public int $capacity = 10;
    public float $refillRate = 1;

    public function __construct(private Redis $redis)
    {
        $this->redis->connect('redis', 6379);
    }

    public function setCapacity(int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function setRefillRate(float $rate): void
    {
        $this->refillRate = $rate;
    }



    public function consume(string $key, int $tokens = 1): bool
    {
        $bucket = $this->getData($key);
        if(!$bucket){
            $this->initBucket($key);
        }
        $this->refill($key);
        $bucket =  $this->getData($key);
        $consume = $bucket->tokens >= $tokens;

        $this->burnTokens($key, $tokens);

        if($consume){
            return true;
        }
        return false;

    }

    public function burnTokens(string $key, int $tokens = 1)
    {
        $redisKey = $this->prefix . $key;
        $bucket = $this->getData($key);
        $now = microtime(true);

        $data = ['tokens' => max(0,$bucket->tokens - $tokens), 'time' => $now];

        $this->redis->set($redisKey, json_encode($data));

    }

    public function refill(string $key)
    {
        $redisKey = $this->prefix . $key;
        $bucket = $this->getData($key);

        $now = microtime(true);

        $time = $bucket->time;

        $addTokens = ($now - $time) * $this->refillRate;

        $newTokens = $bucket->tokens + $addTokens;

        $currentTokens = min(
            $this->capacity,
            $newTokens
        );

        $data = ['tokens' => $currentTokens, 'time' => $now];

        $this->redis->set($redisKey, json_encode($data));

    }

    public function initBucket(string $key)
    {
        $redisKey = $this->prefix . $key;
        $now = microtime(true);

        $data = ['tokens' => $this->capacity, 'time' => $now];

        $this->redis->set($redisKey, json_encode($data));
    }

    public function getData(string $key)
    {
        $redisKey = $this->prefix . $key;

        return json_decode($this->redis->get($redisKey));
    }

    public function getAvailableTokens(string $key): int
    {
        $this->refill($key);
        $bucket = $this->getData($key);

        if (!$bucket || $bucket->tokens === null) {
            return $this->capacity;
        }

        return (int)$bucket->tokens;
    }

//    public function consume(string $key, int $tokens = 1): bool
//    {
//        $redisKey = $this->prefix . $key;
//        $now = microtime(true);
//
//        for ($i = 0; $i < 5; $i++) {
//            $this->redis->watch($redisKey);
//
//            $data = $this->redis->hMGet($redisKey, ['tokens', 'ts']);
//
//            $currentTokens = $data['tokens'] !== null
//                ? (float)$data['tokens']
//                : $this->capacity;
//
//            $lastTs = $data['ts'] !== null
//                ? (float)$data['ts']
//                : $now;
//
//            $elapsed = max(0, $now - $lastTs);
//            $refilled = $elapsed * $this->refillRate;
//
//            $currentTokens = min(
//                $this->capacity,
//                $currentTokens + $refilled
//            );
//
//            if ($currentTokens < $tokens) {
//                $this->redis->unwatch();
//                return false;
//            }
//
//            $newTokens = $currentTokens - $tokens;
//
//            $this->redis->multi();
//            $this->redis->hMSet($redisKey, [
//                'tokens' => $newTokens,
//                'ts' => $now,
//            ]);
//
//            $ttl = (int)ceil($this->capacity / $this->refillRate);
//            $this->redis->expire($redisKey, $ttl);
//
//            $result = $this->redis->exec();
//
//            if ($result !== false) {
//                return true;
//            }
//        }
//
//        throw new RuntimeException('RateLimiter contention too high');
//    }

//    public function getAvailableTokens(string $key): int
//    {
//        $redisKey = $this->prefix . $key;
//
//        $data = $this->redis->hMGet($redisKey, ['tokens', 'ts']);
//
//        if (!$data || $data['tokens'] === null) {
//            return $this->capacity;
//        }
//
//        $currentTokens = (float)$data['tokens'];
//        $lastTs = (float)$data['ts'];
//
//        $now = microtime(true);
//        $elapsed = max(0, $now - $lastTs);
//        $refilled = $elapsed * $this->refillRate;
//
//        return (int)min(
//            $this->capacity,
//            $currentTokens + $refilled
//        );
//    }

    public function reset(string $key): void
    {
        $this->redis->del($this->prefix . $key);
    }
}
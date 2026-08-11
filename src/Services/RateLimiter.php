<?php
declare(strict_types=1);

namespace Akisolu\AnonymousFeedback\Services;

use Predis\Client as RedisClient;

class RateLimiter
{
    public function __construct(
        private RedisClient $redis
    ) {}

    public function tooManyAttempts(string $key, int $maxAttempts): bool {
        $attempts = $this->attempts($key);
        return $attempts >= $maxAttempts;
    }

    public function hit(string $key, int $decaySeconds = 600): int {
        $hits = (int) $this->redis->incr($key);
        if ($hits === 1) {
            $this->redis->expire($key, $decaySeconds);
        }
        return $hits;
    }

    public function attempts(string $key): int {
        $value = $this->redis->get($key);
        if (is_null($value)){
            return 0;
        }
        return (int) $value;
    }

    public function resetAttempts(string $key): void {
        $this->redis->del([$key]);
    }
}
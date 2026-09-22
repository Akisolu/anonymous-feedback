<?php

declare(strict_types=1);

namespace Tests\Support;

use Predis\Client;

class FakeRedisClient extends Client
{
    /** @var array<string, int|string> */
    private array $store = [];

    /** @var array<string, int> */
    private array $expiresAt = [];

    public function __construct()
    {
    }

    public function incr($key): int
    {
        $key = (string) $key;
        $this->pruneExpiredKeys();
        $this->store[$key] = ($this->store[$key] ?? 0) + 1;

        return (int) $this->store[$key];
    }

    public function get($key)
    {
        $key = (string) $key;
        $this->pruneExpiredKeys();

        if (!array_key_exists($key, $this->store)) {
            return null;
        }

        return (string) $this->store[$key];
    }

    public function expire($key, $seconds): int
    {
        $key = (string) $key;
        $this->pruneExpiredKeys();

        if (!array_key_exists($key, $this->store)) {
            return 0;
        }

        $this->expiresAt[$key] = time() + (int) $seconds;

        return 1;
    }

    public function del(...$keys): int
    {
        $items = [];
        foreach ($keys as $key) {
            if (is_array($key)) {
                foreach ($key as $nestedKey) {
                    $items[] = $nestedKey;
                }
                continue;
            }
            $items[] = $key;
        }

        $deleted = 0;
        foreach ($items as $key) {
            $key = (string) $key;
            if (array_key_exists($key, $this->store)) {
                unset($this->store[$key], $this->expiresAt[$key]);
                $deleted++;
            }
        }

        return $deleted;
    }

    public function flush(): void
    {
        $this->store = [];
        $this->expiresAt = [];
    }

    private function pruneExpiredKeys(): void
    {
        $now = time();
        foreach ($this->expiresAt as $key => $expiresAt) {
            if ($expiresAt <= $now) {
                unset($this->store[$key], $this->expiresAt[$key]);
            }
        }
    }
}

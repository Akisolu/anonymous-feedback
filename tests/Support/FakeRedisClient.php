<?php

declare(strict_types=1);

namespace Tests\Support;

use Predis\Client;

class FakeRedisClient extends Client
{
    /** @var array<string, int> */
    private array $store = [];

    public function __construct()
    {
    }

    public function incr($key): int
    {
        $key = (string) $key;
        $this->store[$key] = ($this->store[$key] ?? 0) + 1;

        return $this->store[$key];
    }

    public function get($key)
    {
        $key = (string) $key;

        return $this->store[$key] ?? null;
    }

    public function expire($key, $seconds): bool
    {
        return true;
    }

    public function del($keys): int
    {
        $items = is_array($keys) ? $keys : [$keys];
        $deleted = 0;

        foreach ($items as $key) {
            $key = (string) $key;
            if (array_key_exists($key, $this->store)) {
                unset($this->store[$key]);
                $deleted++;
            }
        }

        return $deleted;
    }

    public function flush(): void
    {
        $this->store = [];
    }
}

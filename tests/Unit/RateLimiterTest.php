<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Akisolu\AnonymousFeedback\Services\RateLimiter;
use Tests\Support\FakeRedisClient;

class RateLimiterTest extends TestCase
{
    private FakeRedisClient $redis;
    private RateLimiter $rateLimiter;
    private string $testKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->redis = new FakeRedisClient();
        $this->rateLimiter = new RateLimiter($this->redis);
        $this->testKey = 'test_rate_limit:' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        $this->redis->flush();
        parent::tearDown();
    }

    public function test_allows_requests_under_limit(): void
    {
        for ($i = 0; $i < 9; $i++) {
            $this->rateLimiter->hit($this->testKey, 600);
        }

        $this->assertEquals(9, $this->rateLimiter->attempts($this->testKey));
        $this->assertFalse($this->rateLimiter->tooManyAttempts($this->testKey, 10));
    }

    public function test_blocks_requests_exceeding_limit(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->rateLimiter->hit($this->testKey, 600);
        }

        $this->assertTrue($this->rateLimiter->tooManyAttempts($this->testKey, 10));
        $this->assertEquals(10, $this->rateLimiter->attempts($this->testKey));
    }

    public function test_can_reset_attempts(): void
    {
        $this->rateLimiter->hit($this->testKey, 600);
        $this->rateLimiter->hit($this->testKey, 600);
        $this->rateLimiter->hit($this->testKey, 600);

        $this->rateLimiter->resetAttempts($this->testKey);

        $this->assertEquals(0, $this->rateLimiter->attempts($this->testKey));
    }
}
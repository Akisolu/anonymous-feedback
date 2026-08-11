<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Akisolu\AnonymousFeedback\Services\RateLimiter;
use Predis\Client as RedisClient;
use Psr\Container\ContainerInterface;

class RateLimiterTest extends TestCase {
    private ContainerInterface $container;
    private RedisClient $redis;
    private RateLimiter $rateLimiter;
    private string $testKey;

    public function setUp(): void {
        $this->container = require __DIR__ . '/../../config/container.php';
        $this->redis = $this->container->get(RedisClient::class);
        $this->rateLimiter = new RateLimiter($this->redis);
        $this->testKey = 'test_rate_limit:' . uniqid();
    }

    public function tearDown(): void {
        $this->redis->del($this->testKey);
    }

    public function test_allows_requests_under_limit() {
        $this->rateLimiter->tooManyAttempts($this->testKey, 10);
        for ($i = 0; $i < 5; $i++) {
            $this->rateLimiter->hit($this->testKey, 600);
        }
        $this->assertEquals(5, $this->rateLimiter->attempts($this->testKey));
        $this->assertFalse($this->rateLimiter->tooManyAttempts($this->testKey, 10));
        }
        
    public function test_blocks_requests_exceeding_limit() {
        for ($i = 1; $i < 10; $i++){
            $this->rateLimiter->hit($this->testKey, 600);
        }
        $this->assertFalse($this->rateLimiter->tooManyAttempts($this->testKey, 10));
        $this->rateLimiter->hit($this->testKey, 600);
        $this->assertTrue($this->rateLimiter->tooManyAttempts($this->testKey, 10));
        $this->assertEquals(10, $this->rateLimiter->attempts($this->testKey));
        }
        
        public function test_can_reset_attempts() {
        $this->rateLimiter->hit($this->testKey);
        $this->rateLimiter->hit($this->testKey);
        $this->rateLimiter->hit($this->testKey);
        $this->rateLimiter->resetAttempts($this->testKey);
        $this->assertEquals(0, $this->rateLimiter->attempts($this->testKey));
    }
}
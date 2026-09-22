<?php

declare(strict_types=1);

namespace Tests\Integration;

use PDO;
use Illuminate\Database\Capsule\Manager as Capsule;
use Predis\Client as RedisClient;
use Akisolu\AnonymousFeedback\Services\RateLimiter;

class DatabaseConnectionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireService(PDO::class, 'PostgreSQL');
    }

    public function testPdoPostgresConnectionIsSuccessful(): void
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get(PDO::class);

        $this->assertInstanceOf(PDO::class, $pdo);
        $version = $pdo->query('SELECT version()')->fetchColumn();
        $this->assertIsString($version);
        $this->assertStringContainsString('PostgreSQL', $version);
    }

    public function testEloquentCapsuleConnectionIsSuccessful(): void
    {
        /** @var Capsule $capsule */
        $capsule = $this->container->get(Capsule::class);

        $this->assertInstanceOf(Capsule::class, $capsule);
        $result = $capsule::select('SELECT 1 as alive');
        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result[0]->alive);
    }

    public function testRedisConnectionAndOperationsAreSuccessful(): void
    {
        $this->requireService(RedisClient::class, 'Redis');

        /** @var RedisClient $redis */
        $redis = $this->container->get(RedisClient::class);

        $this->assertInstanceOf(RedisClient::class, $redis);
        $ping = $redis->ping();
        $this->assertTrue($ping == 'PONG' || $ping === true);

        $testKey = 'unit_test_key_' . uniqid();
        $redis->set($testKey, 'Hello Redis PHPUnit');
        $val = $redis->get($testKey);
        $this->assertEquals('Hello Redis PHPUnit', $val);
        $redis->del([$testKey]);
    }

    public function testRateLimiterCanBeResolvedFromContainer(): void
    {
        $this->requireService(RedisClient::class, 'Redis');

        /** @var RateLimiter $rateLimiter */
        $rateLimiter = $this->container->get(RateLimiter::class);

        $this->assertInstanceOf(RateLimiter::class, $rateLimiter);
        $this->assertFalse($rateLimiter->tooManyAttempts('container_test_key_' . uniqid(), 5));
    }
}

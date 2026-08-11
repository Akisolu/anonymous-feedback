<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use PDO;
use Illuminate\Database\Capsule\Manager as Capsule;
use Predis\Client as RedisClient;

class DatabaseConnectionTest extends TestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = require __DIR__ . '/../../config/container.php';
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
}
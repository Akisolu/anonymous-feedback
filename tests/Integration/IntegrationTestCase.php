<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Throwable;

abstract class IntegrationTestCase extends TestCase
{
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->container = require __DIR__ . '/../../config/container.php';
            $this->container->get(\PDO::class);
        } catch (Throwable $exception) {
            $this->markTestSkipped(sprintf(
                'Integration services are not available: %s',
                $exception->getMessage()
            ));
        }
    }
}

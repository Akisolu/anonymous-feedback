<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use PDOException;

abstract class IntegrationTestCase extends TestCase
{
    protected ContainerInterface $container;

    protected function loadContainer(): void
    {
        $this->container = require __DIR__ . '/../../config/container.php';
    }

    protected function requireService(string $serviceClass, string $serviceName): void
    {
        try {
            $this->loadContainer();
            $this->container->get($serviceClass);
        } catch (PDOException $exception) {
            $this->markTestSkipped(sprintf(
                'Required integration service "%s" is not available: %s',
                $serviceName,
                $exception->getMessage()
            ));
        }
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Akisolu\AnonymousFeedback\Repositories\FeedbackRepositoryInterface;

class FeedbackRepositoryTest extends TestCase
{
    private ContainerInterface $container;
    private FeedbackRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ContainerInterface $container */
        $this->container = require __DIR__ . '/../../config/container.php';
        $this->repository = $this->container->get(FeedbackRepositoryInterface::class);
    }

    public function testFeedbackRepositoryCanBeResolvedFromContainer(): void
    {
        $feedbackRepository = $this->container->get(FeedbackRepositoryInterface::class);

        $this->assertInstanceOf(FeedbackRepositoryInterface::class, $feedbackRepository);
    }

    public function testCanCreateAndFindFeedbackViaInterface(): void
    {
        $messageContent = 'Test message sent via the interface and PHP-DI.';

        $created = $this->repository->create($messageContent);

        $this->assertIsArray($created);
        $this->assertArrayHasKey('feedback_id', $created);
        $this->assertEquals($messageContent, $created['message']);

        $found = $this->repository->findById((int) $created['feedback_id']);

        $this->assertIsArray($found);
        $this->assertEquals($created['feedback_id'], $found['feedback_id']);
        $this->assertEquals($messageContent, $found['message']);
    }
}
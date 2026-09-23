<?php

declare(strict_types=1);

namespace Tests\Integration;

use Akisolu\AnonymousFeedback\Controllers\FeedbackController;
use Akisolu\AnonymousFeedback\Services\RateLimiter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FeedbackControllerTest extends TestCase
{
    private ContainerInterface $container;
    private RateLimiter $rateLimiter;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ContainerInterface $container */
        $this->container = require __DIR__ . '/../../config/container.php';
        $this->rateLimiter = $this->container->get(RateLimiter::class);
    }

    public function testStoreCreatesFeedbackAndReturns201Created(): void
    {
        $ip = '10.0.0.1';
        $this->rateLimiter->resetAttempts("feedback_rate_limit:{$ip}");

        /** @var FeedbackController $controller */
        $controller = $this->container->get(FeedbackController::class);

        $payload = json_encode(['message' => 'Feedback de prueba integración.']);
        $request = Request::create(
            '/api/feedbacks',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => $ip],
            $payload
        );

        $response = $controller->store($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $data = json_decode((string) $response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Feedback de prueba integración.', $data['data']['message']);
    }

   public function testStoreReturns429WhenRateLimitIsExceeded(): void
{
    $ip = '10.0.0.2';
    $key = "feedback_rate_limit:{$ip}";
    $this->rateLimiter->resetAttempts($key);

    $maxRequests = (isset($_ENV['RATE_LIMIT_MAX_REQUESTS']) && $_ENV['RATE_LIMIT_MAX_REQUESTS'] !== '')
        ? max(1, (int) $_ENV['RATE_LIMIT_MAX_REQUESTS'])
        : 10;

    for ($i = 0; $i < $maxRequests; $i++) {
        $this->rateLimiter->hit($key);
    }

    /** @var FeedbackController $controller */
    $controller = $this->container->get(FeedbackController::class);

    $payload = json_encode(['message' => 'Debería rebotar por rate limit.']);
    $request = Request::create(
        '/api/feedbacks',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => $ip],
        $payload
    );

    $response = $controller->store($request);

    $this->assertEquals(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
}

    public function testStoreReturns422WhenMessageIsEmpty(): void
    {
        /** @var FeedbackController $controller */
        $controller = $this->container->get(FeedbackController::class);

        $payload = json_encode(['message' => '']);
        $request = Request::create('/api/feedbacks', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $response = $controller->store($request);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testStoreReturns422WhenMessageExceedsMaxLength(): void
    {
        /** @var FeedbackController $controller */
        $controller = $this->container->get(FeedbackController::class);

        $payload = json_encode(['message' => str_repeat('b', 1001)]);
        $request = Request::create('/api/feedbacks', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $response = $controller->store($request);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }
}
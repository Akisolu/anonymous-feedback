<?php

declare(strict_types=1);

namespace Tests\Unit;

use Akisolu\AnonymousFeedback\Controllers\FeedbackController;
use Akisolu\AnonymousFeedback\Repositories\FeedbackRepositoryInterface;
use Akisolu\AnonymousFeedback\Services\RateLimiter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FeedbackControllerTest extends TestCase
{
    private const MAX_REQUESTS = 10;
    private const DECAY_SECONDS = 600;

    public function testConstructorFallbackBehaviorWithEnvironmentVariables(): void
{
    $repositoryMock = $this->createMock(FeedbackRepositoryInterface::class);
    $rateLimiterMock = $this->createMock(RateLimiter::class);

    $_ENV['RATE_LIMIT_MAX_REQUESTS'] = '';
    $_ENV['RATE_LIMIT_DECAY'] = '';
    $controller1 = new FeedbackController($repositoryMock, $rateLimiterMock);

    $rateLimiterMock->expects($this->once())
        ->method('tooManyAttempts')
        ->with('feedback_rate_limit:127.0.0.1', 10)
        ->willReturn(false);

    $request = Request::create('/api/feedbacks', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['message' => 'Test']));
    $controller1->store($request);

    unset($_ENV['RATE_LIMIT_MAX_REQUESTS'], $_ENV['RATE_LIMIT_DECAY']);
    $controller2 = new FeedbackController($repositoryMock, $rateLimiterMock);

    $rateLimiterMock->expects($this->once())
        ->method('tooManyAttempts')
        ->with('feedback_rate_limit:127.0.0.1', 10)
        ->willReturn(false);

    $controller2->store($request);

    $_ENV['RATE_LIMIT_MAX_REQUESTS'] = '15';
    $_ENV['RATE_LIMIT_DECAY'] = '300';
    $controller3 = new FeedbackController($repositoryMock, $rateLimiterMock);

    $rateLimiterMock->expects($this->once())
        ->method('tooManyAttempts')
        ->with('feedback_rate_limit:127.0.0.1', 15)
        ->willReturn(false);

    $controller3->store($request);
}

    public function testStoreCreatesFeedbackWhenRateLimitIsNotExceeded(): void
    {
        $ip = '127.0.0.1';
        $key = "feedback_rate_limit:{$ip}";

        $rateLimiterMock = $this->createMock(RateLimiter::class);
        $rateLimiterMock->expects($this->once())
            ->method('tooManyAttempts')
            ->with($key, self::MAX_REQUESTS)
            ->willReturn(false);

        $rateLimiterMock->expects($this->once())
            ->method('hit')
            ->with($key, self::DECAY_SECONDS);

        $repositoryMock = $this->createMock(FeedbackRepositoryInterface::class);
        $repositoryMock->expects($this->once())
            ->method('create')
            ->with('Mensaje simulado por mock unitario')
            ->willReturn([
                'feedback_id' => 99,
                'message'     => 'Mensaje simulado por mock unitario',
                'state_id'    => 1,
                'created_at'  => '2026-09-22 20:00:00',
            ]);

        $controller = new FeedbackController(
            $repositoryMock, 
            $rateLimiterMock, 
            self::MAX_REQUESTS, 
            self::DECAY_SECONDS
        );

        $payload = json_encode(['message' => 'Mensaje simulado por mock unitario']);
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
        $this->assertEquals(99, $data['data']['feedback_id']);
    }

    public function testStoreReturns429WhenRateLimitIsExceeded(): void
    {
        $ip = '127.0.0.1';
        $key = "feedback_rate_limit:{$ip}";

        $rateLimiterMock = $this->createMock(RateLimiter::class);
        $rateLimiterMock->expects($this->once())
            ->method('tooManyAttempts')
            ->with($key, self::MAX_REQUESTS)
            ->willReturn(true);

        $rateLimiterMock->expects($this->never())->method('hit');

        $repositoryMock = $this->createMock(FeedbackRepositoryInterface::class);
        $repositoryMock->expects($this->never())->method('create');

        $controller = new FeedbackController(
            $repositoryMock, 
            $rateLimiterMock, 
            self::MAX_REQUESTS, 
            self::DECAY_SECONDS
        );

        $payload = json_encode(['message' => 'Intento bloqueado']);
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
        $data = json_decode((string) $response->getContent(), true);
        $this->assertEquals('Too many requests. Please try again later.', $data['error']);
    }

    public function testStoreReturns422WhenMessageIsEmpty(): void
    {
        $rateLimiterMock = $this->createMock(RateLimiter::class);
        $rateLimiterMock->method('tooManyAttempts')->willReturn(false);

        $repositoryMock = $this->createMock(FeedbackRepositoryInterface::class);
        $repositoryMock->expects($this->never())->method('create');

        $controller = new FeedbackController(
            $repositoryMock,
            $rateLimiterMock,
            self::MAX_REQUESTS,
            self::DECAY_SECONDS
        );

        $payload = json_encode(['message' => '   ']);
        $request = Request::create('/api/feedbacks', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $response = $controller->store($request);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testStoreReturns422WhenMessageExceedsMaxLength(): void
    {
        $rateLimiterMock = $this->createMock(RateLimiter::class);
        $rateLimiterMock->method('tooManyAttempts')->willReturn(false);

        $repositoryMock = $this->createMock(FeedbackRepositoryInterface::class);
        $repositoryMock->expects($this->never())->method('create');

        $controller = new FeedbackController(
            $repositoryMock,
            $rateLimiterMock,
            self::MAX_REQUESTS,
            self::DECAY_SECONDS
        );

        $payload = json_encode(['message' => str_repeat('a', 1001)]);
        $request = Request::create('/api/feedbacks', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $response = $controller->store($request);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }
}
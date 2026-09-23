<?php

declare(strict_types=1);

namespace Akisolu\AnonymousFeedback\Controllers;

use Akisolu\AnonymousFeedback\Repositories\FeedbackRepositoryInterface;
use Akisolu\AnonymousFeedback\Services\RateLimiter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FeedbackController
{
    private int $maxAttempts;
    private int $decaySeconds;

    public function __construct(
        private readonly FeedbackRepositoryInterface $feedbackRepository,
        private readonly RateLimiter $rateLimiter,
        ?int $maxAttempts = null,
        ?int $decaySeconds = null
    ) {
        $this->maxAttempts = $maxAttempts ?? (
            isset($_ENV['RATE_LIMIT_MAX_REQUESTS']) && $_ENV['RATE_LIMIT_MAX_REQUESTS'] !== ''
                ? max(1, (int) $_ENV['RATE_LIMIT_MAX_REQUESTS'])
                : 10
        );

        $this->decaySeconds = $decaySeconds ?? (
            isset($_ENV['RATE_LIMIT_DECAY']) && $_ENV['RATE_LIMIT_DECAY'] !== ''
                ? max(1, (int) $_ENV['RATE_LIMIT_DECAY'])
                : 600
        );
    }

    public function store(Request $request): JsonResponse
    {
        $clientIp = $request->getClientIp() ?? '127.0.0.1';
        $rateLimitKey = "feedback_rate_limit:{$clientIp}";

        if ($this->rateLimiter->tooManyAttempts($rateLimitKey, $this->maxAttempts)) {
            return new JsonResponse([
                'error' => 'Too many requests. Please try again later.'
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $data = json_decode($request->getContent(), true);
        $message = is_array($data) && is_string($data['message'] ?? null)
            ? trim($data['message'])
            : '';

        if ($message === '' || mb_strlen($message) > 1000) {
            return new JsonResponse([
                'error' => 'Invalid feedback message. Must be between 1 and 1000 characters.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->rateLimiter->hit($rateLimitKey, $this->decaySeconds);

        $feedback = $this->feedbackRepository->create($message);

        return new JsonResponse([
            'status' => 'success',
            'data'   => $feedback,
        ], Response::HTTP_CREATED);
    }
}
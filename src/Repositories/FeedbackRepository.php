<?php

declare(strict_types=1);

namespace Akisolu\AnonymousFeedback\Repositories;

use Akisolu\AnonymousFeedback\Models\Feedback;

class FeedbackRepository implements FeedbackRepositoryInterface
{
    public function create(string $message): array
    {
        $feedback = Feedback::create([
            'message'  => $message,
        ]);

        return $feedback->toArray();
    }

    public function findById(int $id): ?array
    {
        $feedback = Feedback::find($id);

        return $feedback ? $feedback->toArray() : null;
    }
}
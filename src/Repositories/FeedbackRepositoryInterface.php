<?php

declare(strict_types=1);

namespace Akisolu\AnonymousFeedback\Repositories;

interface FeedbackRepositoryInterface
{
    /**
     * A new anonymous feedback message remains in the database.
     *
     * @param string $message
     * @return array Associative array with feedback_id, message, state_id and created_at. 
*/
    public function create(string $message): array;

    /**
     * Gets a feedback by its unique identifier.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array;
}
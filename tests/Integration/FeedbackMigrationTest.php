<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use PDO;

class FeedbackMigrationTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var ContainerInterface $container */
        $container = require __DIR__ . '/../../config/container.php';
        $this->pdo = $container->get(PDO::class);
    }

    public function test_trigger_records_state_change_automatically(): void
    {
        // 1. Insert anonymous feedback
        $stmt = $this->pdo->prepare("INSERT INTO feedbacks (message) VALUES (:msg) RETURNING feedback_id");
        $stmt->execute(['msg' => 'TDD test feedback']);
        $feedbackId = (int) $stmt->fetchColumn();

        $this->assertGreaterThan(0, $feedbackId);

        // 2. Simulate an authenticated user in the app session in Postgres
        $this->pdo->exec("SET LOCAL app.current_user_id = '1'");

        // 3. Change the state from 1 (unread) to 2 (read)
        $updateStmt = $this->pdo->prepare("UPDATE feedbacks SET state_id = 2 WHERE feedback_id = :id");
        $updateStmt->execute(['id' => $feedbackId]);

        // 4. Verify that the TRIGGER created the audit log in feedback_records
        $auditStmt = $this->pdo->prepare("SELECT * FROM feedback_records WHERE feedback_id = :id");
        $auditStmt->execute(['id' => $feedbackId]);
        $record = $auditStmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($record);
        $this->assertEquals(1, $record['user_id']);
        $this->assertEquals(1, $record['old_state_id']);
        $this->assertEquals(2, $record['new_state_id']);

        // Clean
        $this->pdo->prepare("DELETE FROM feedbacks WHERE feedback_id = :id")->execute(['id' => $feedbackId]);
    }
}
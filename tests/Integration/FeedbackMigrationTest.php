<?php

declare(strict_types=1);

namespace Tests\Integration;

use PDO;

class FeedbackMigrationTest extends IntegrationTestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requireService(PDO::class, 'PostgreSQL');
        $this->pdo = $this->container->get(PDO::class);
    }

    public function test_trigger_records_state_change_automatically(): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO feedbacks (message) VALUES (:msg) RETURNING feedback_id");
        $stmt->execute(['msg' => 'TDD test feedback']);
        $feedbackId = (int) $stmt->fetchColumn();

        $this->assertGreaterThan(0, $feedbackId);
        $this->pdo->exec("SET LOCAL app.current_user_id = '1'");

        $updateStmt = $this->pdo->prepare("UPDATE feedbacks SET state_id = 2 WHERE feedback_id = :id");
        $updateStmt->execute(['id' => $feedbackId]);

        $auditStmt = $this->pdo->prepare("SELECT * FROM feedback_records WHERE feedback_id = :id");
        $auditStmt->execute(['id' => $feedbackId]);
        $record = $auditStmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($record);
        $this->assertEquals(1, $record['user_id']);
        $this->assertEquals(1, $record['old_state_id']);
        $this->assertEquals(2, $record['new_state_id']);

        $this->pdo->prepare("DELETE FROM feedbacks WHERE feedback_id = :id")->execute(['id' => $feedbackId]);
    }
}

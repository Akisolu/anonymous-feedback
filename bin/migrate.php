<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';
$pdo = $container->get(PDO::class);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sqlPath = __DIR__ . '/../schema.sql';

if (!file_exists($sqlPath)) {
    echo "\033[31m[ERROR] The migration file was not found in: {$sqlPath}\033[0m\n";
    exit(1);
}

echo "\033[33mRunning migration in PostgreSQL...\033[0m\n";

try {
    $sql = file_get_contents($sqlPath);
    
    // Execute the complete SQL within an explicit transaction
    $pdo->beginTransaction();
    $pdo->exec($sql);
    $pdo->commit();

    echo "\033[32m[SUCCESS] All tables, indexes, triggers, and initial records were successfully created!\033[0m\n";
} catch (\PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\033[31m[MIGRATION ERROR]\033[0m\n";
    echo "Message: " . $e->getMessage() . "\n";
    exit(1);
}
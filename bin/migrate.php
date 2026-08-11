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
    echo "\033[31m[ERROR] No se encontró el archivo de migración en: {$sqlPath}\033[0m\n";
    exit(1);
}

echo "\033[33mEjecutando migración en PostgreSQL...\033[0m\n";

try {
    $sql = file_get_contents($sqlPath);
    
    // Ejecutar el SQL completo dentro de una transacción explícita
    $pdo->beginTransaction();
    $pdo->exec($sql);
    $pdo->commit();

    echo "\033[32m[ÉXITO] ¡Todas las tablas, índices, triggers y registros iniciales fueron creados exitosamente!\033[0m\n";
} catch (\PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\033[31m[ERROR EN LA MIGRACIÓN]\033[0m\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    exit(1);
}
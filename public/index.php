<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

require __DIR__ . '/../vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

header('Content-Type: application/json');

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Ruta de prueba para verificar el estado de la API
if ($uri === '/ping' && $method === 'GET') {
    echo json_encode(['status' => 'ok', 'message' => 'Prototipo activo en Docker']);
    exit;
}

// Endpoint del prototipo para registrar feedback anónimo
if ($uri === '/api/feedbacks' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['message'])) {
        http_response_code(400);
        echo json_encode(['error' => 'El campo message es obligatorio']);
        exit;
    }

    /** @var PDO $pdo */
    $pdo = $container->get(PDO::class);

    $stmt = $pdo->prepare("INSERT INTO feedbacks (message) VALUES (:msg) RETURNING feedback_id, created_at");
    $stmt->execute(['msg' => $input['message']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(201);
    echo json_encode([
        'message' => 'Feedback registrado exitosamente',
        'data' => [
            'feedback_id' => (int) $result['feedback_id'],
            'content' => $input['message'],
            'created_at' => $result['created_at'],
        ]
    ]);
    exit;
}

// Ruta no encontrada
http_response_code(404);
echo json_encode(['error' => 'Ruta no encontrada']);
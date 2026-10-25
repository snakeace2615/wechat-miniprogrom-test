<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path === '/health') {
    echo json_encode(['status' => 'ok'], JSON_THROW_ON_ERROR);
    exit;
}

// Lightweight endpoint for verifying the GitHub Actions deployment.
if ($path === '/ping') {
    echo json_encode(['message' => 'pong'], JSON_THROW_ON_ERROR);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found'], JSON_THROW_ON_ERROR);

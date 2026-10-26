<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path === '/health') {
    echo json_encode(['status' => 'ok'], JSON_THROW_ON_ERROR);
    exit;
}

if ($path === '/health/database') {
    try {
        $database = require dirname(__DIR__) . '/config/database.php';
        $database->query('SELECT 1');
        echo json_encode(['status' => 'ok', 'database' => 'ok'], JSON_THROW_ON_ERROR);
    } catch (Throwable $exception) {
        error_log('Database health check failed: ' . $exception::class . ' (' . $exception->getCode() . ')');
        http_response_code(503);
        echo json_encode(['status' => 'error', 'database' => 'unavailable'], JSON_THROW_ON_ERROR);
    }
    exit;
}

if ($path === '/users') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed'], JSON_THROW_ON_ERROR);
        exit;
    }

    try {
        $input = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON'], JSON_THROW_ON_ERROR);
        exit;
    }

    if (!is_array($input)
        || !isset($input['username'], $input['password'])
        || !is_string($input['username'])
        || !is_string($input['password'])
        || trim($input['username']) === ''
        || strlen(trim($input['username'])) > 100
        || str_contains($input['username'], "\0")
        || $input['password'] === ''
        || strlen($input['password']) > 72
        || str_contains($input['password'], "\0")
    ) {
        http_response_code(400);
        echo json_encode([
            'error' => 'username must be 1-100 bytes after trimming; password must be 1-72 bytes; neither may contain null bytes',
        ], JSON_THROW_ON_ERROR);
        exit;
    }

    try {
        $passwordHash = password_hash($input['password'], PASSWORD_BCRYPT);
        $database = require dirname(__DIR__) . '/config/database.php';
        $statement = $database->prepare(
            'INSERT INTO users (username, password_hash) VALUES (:username, :password_hash) RETURNING id, username',
        );
        $statement->execute([
            'username' => trim($input['username']),
            'password_hash' => $passwordHash,
        ]);
        $user = $statement->fetch();

        http_response_code(201);
        echo json_encode(['id' => (int) $user['id'], 'username' => $user['username']], JSON_THROW_ON_ERROR);
    } catch (Throwable $exception) {
        if ($exception instanceof PDOException && $exception->getCode() === '23505') {
            http_response_code(409);
            echo json_encode(['error' => 'Username already exists'], JSON_THROW_ON_ERROR);
        } else {
            error_log('User creation failed: ' . $exception::class . ' (' . $exception->getCode() . ')');
            http_response_code(503);
            echo json_encode(['error' => 'Unable to save user'], JSON_THROW_ON_ERROR);
        }
    }
    exit;
}

// Lightweight endpoint for verifying the GitHub Actions deployment.
if ($path === '/ping') {
    echo json_encode(['message' => 'pong'], JSON_THROW_ON_ERROR);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found'], JSON_THROW_ON_ERROR);

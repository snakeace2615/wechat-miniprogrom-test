<?php
declare(strict_types=1);

$config = @parse_ini_file(dirname(__DIR__) . '/.env', false, INI_SCANNER_RAW);
if ($config === false) {
    throw new RuntimeException('Cannot read database configuration.');
}

foreach (['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_SSLMODE'] as $key) {
    if (!isset($config[$key]) || !is_string($config[$key]) || $config[$key] === '') {
        throw new RuntimeException('Missing database configuration: ' . $key);
    }
}

return new PDO(
    sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s;connect_timeout=5',
        $config['DB_HOST'],
        $config['DB_PORT'],
        $config['DB_DATABASE'],
        $config['DB_SSLMODE'],
    ),
    $config['DB_USERNAME'],
    $config['DB_PASSWORD'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
);

<?php
declare(strict_types=1);

$database = require dirname(__DIR__) . '/config/database.php';
$database->exec(<<<'SQL'
    CREATE TABLE IF NOT EXISTS users (
        id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL
    )
    SQL);

echo "Database schema is ready.\n";

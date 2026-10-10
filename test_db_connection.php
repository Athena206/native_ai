<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/db_connection.php';

try {
    getDatabaseConnection();
    fwrite(STDOUT, "Database connection successful.\n");
} catch (RuntimeException $exception) {
    fwrite(STDERR, "Database connection failed: {$exception->getMessage()}\n");
    exit(1);
}

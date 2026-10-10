<?php

declare(strict_types=1);

/**
 * Create and return a PDO connection to the chatbot database.
 */
function getDatabaseConnection(): PDO
{
    $host = getenv('SBI_DB_HOST') ?: '127.0.0.1';
    $port = getenv('SBI_DB_PORT') ?: '3306';
    $username = getenv('SBI_DB_USER');
    $password = getenv('SBI_DB_PASSWORD');

    if ($username === false || $username === '' || $password === false || $password === '') {
        throw new RuntimeException('Database credentials are not configured.');
    }

    if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
        throw new RuntimeException('The database port configuration is invalid.');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=sbi_chatbot_db;charset=utf8mb4',
        $host,
        (int) $port
    );

    try {
        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
        ]);
    } catch (PDOException $exception) {
        error_log('MySQL connection failed: ' . $exception->getMessage());
        throw new RuntimeException('Unable to connect to the database.');
    }
}

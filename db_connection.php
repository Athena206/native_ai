<?php

declare(strict_types=1);

/**
 * Load optional machine-local configuration that is excluded from version control.
 */
function getLocalAppConfig(): array
{
    $configPath = __DIR__ . '/config.local.php';
    if (!is_file($configPath)) {
        return [];
    }

    $config = require $configPath;
    if (!is_array($config)) {
        throw new RuntimeException('The local configuration file must return an array.');
    }

    return $config;
}

/**
 * Create and return a PDO connection to the chatbot database.
 */
function getDatabaseConnection(): PDO
{
    $config = getLocalAppConfig();
    $databaseConfig = is_array($config['database'] ?? null) ? $config['database'] : [];
    $host = getenv('SBI_DB_HOST') ?: ($databaseConfig['host'] ?? '127.0.0.1');
    $port = getenv('SBI_DB_PORT') ?: ($databaseConfig['port'] ?? '3306');
    $username = getenv('SBI_DB_USER');
    $password = getenv('SBI_DB_PASSWORD');

    if ($username === false || $username === '') {
        $username = $databaseConfig['user'] ?? '';
    }
    if ($password === false || $password === '') {
        $password = $databaseConfig['password'] ?? '';
    }

    if (
        !is_string($host)
        || !is_string($port)
        || !is_string($username)
        || !is_string($password)
        || $username === ''
        || $password === ''
    ) {
        throw new RuntimeException('Database credentials are not configured.');
    }

    if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
        throw new RuntimeException('The database port configuration is invalid.');
    }

    if (!extension_loaded('pdo_mysql')) {
        throw new RuntimeException('The PHP PDO MySQL extension is not installed.');
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

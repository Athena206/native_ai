<?php

declare(strict_types=1);

require_once __DIR__ . '/db_connection.php';

function startAuthSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function getAuthenticatedUser(): ?array
{
    startAuthSession();
    $user = $_SESSION['user'] ?? null;
    if (!is_array($user) || !isset($user['id'], $user['name'], $user['email'])) {
        return null;
    }

    return [
        'id' => (int) $user['id'],
        'name' => (string) $user['name'],
        'email' => (string) $user['email'],
    ];
}

function ensureUsersAuthSchema(PDO $pdo): void
{
    $emailCol = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($emailCol !== false && $emailCol->fetch() === false) {
        $pdo->exec('ALTER TABLE users ADD COLUMN email VARCHAR(190) NULL DEFAULT NULL AFTER name, ADD UNIQUE KEY uq_users_email (email)');
    }

    $passCol = $pdo->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
    if ($passCol !== false && $passCol->fetch() === false) {
        $pdo->exec('ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NULL DEFAULT NULL AFTER email');
    }

    $phoneCol = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
    if ($phoneCol !== false) {
        $phoneInfo = $phoneCol->fetch();
        if (is_array($phoneInfo) && strtoupper((string) ($phoneInfo['Null'] ?? '')) === 'NO') {
            $pdo->exec('ALTER TABLE users MODIFY phone VARCHAR(20) NULL DEFAULT NULL');
        }
    }
}

function sendAuthJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    startAuthSession();

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        $user = getAuthenticatedUser();
        sendAuthJson(200, [
            'authenticated' => $user !== null,
            'user' => $user,
        ]);
    }

    if ($method !== 'POST') {
        header('Allow: GET, POST');
        sendAuthJson(405, ['ok' => false, 'error' => 'Method not allowed.']);
    }

    $rawBody = file_get_contents('php://input');
    $data = [];
    if (is_string($rawBody) && trim($rawBody) !== '') {
        try {
            $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        } catch (JsonException $e) {
            sendAuthJson(400, ['ok' => false, 'error' => 'Invalid JSON request.']);
        }
    } else {
        $data = $_POST;
    }

    $action = strtolower(trim((string) ($data['action'] ?? '')));

    if ($action === 'logout') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        sendAuthJson(200, ['ok' => true, 'message' => 'Logged out successfully.']);
    }

    try {
        $pdo = getDatabaseConnection();
        ensureUsersAuthSchema($pdo);
    } catch (Throwable $e) {
        error_log('Auth DB error: ' . $e->getMessage());
        sendAuthJson(500, ['ok' => false, 'error' => 'Database connection is unavailable. Please try again.']);
    }

    if ($action === 'register') {
        $name = trim((string) ($data['name'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');

        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            sendAuthJson(422, ['ok' => false, 'error' => 'Please enter your full name (at least 2 characters).']);
        }
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 190) {
            sendAuthJson(422, ['ok' => false, 'error' => 'Please enter a valid email address.']);
        }
        if (strlen($password) < 6) {
            sendAuthJson(422, ['ok' => false, 'error' => 'Password must be at least 6 characters long.']);
        }

        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $checkStmt->execute(['email' => $email]);
        if ($checkStmt->fetch() !== false) {
            sendAuthJson(409, ['ok' => false, 'error' => 'This email is already registered. Please log in instead.']);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $pdo->prepare(
            'INSERT INTO users (name, email, password_hash, phone) VALUES (:name, :email, :password_hash, NULL)'
        );
        $insertStmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $hash,
        ]);

        sendAuthJson(201, [
            'ok' => true,
            'message' => 'Account created successfully! Please log in with your email and password.',
        ]);
    }

    if ($action === 'login') {
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            sendAuthJson(422, ['ok' => false, 'error' => 'Please enter your registered email address.']);
        }
        if ($password === '') {
            sendAuthJson(422, ['ok' => false, 'error' => 'Please enter your password.']);
        }

        $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!is_array($user) || empty($user['password_hash']) || !password_verify($password, (string) $user['password_hash'])) {
            sendAuthJson(401, ['ok' => false, 'error' => 'Incorrect email or password. Please check and try again.']);
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
        ];

        sendAuthJson(200, [
            'ok' => true,
            'message' => 'Login successful! Welcome back.',
            'user' => $_SESSION['user'],
        ]);
    }

    sendAuthJson(400, ['ok' => false, 'error' => 'Unsupported action.']);
}

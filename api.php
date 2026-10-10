<?php

declare(strict_types=1);

require_once __DIR__ . '/db_connection.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(405, ['error' => 'Method not allowed. Use POST.']);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== 0) {
    respond(415, ['error' => 'Content-Type must be application/json.']);
}

$requestBody = file_get_contents('php://input');
if ($requestBody === false || $requestBody === '') {
    respond(400, ['error' => 'A JSON request body is required.']);
}

try {
    $request = json_decode($requestBody, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    respond(400, ['error' => 'Request body must contain valid JSON.']);
}

if (!is_array($request) || !isset($request['message']) || !is_string($request['message'])) {
    respond(400, ['error' => 'The JSON body must include a string message field.']);
}

$message = trim($request['message']);
if ($message === '') {
    respond(400, ['error' => 'The message field must not be empty.']);
}

if (strlen($message) > 16000) {
    respond(413, ['error' => 'The message exceeds the 16 KB limit.']);
}

$fallbackAnswer = 'I can help you with digital banking. Please contact SBI support or visit our YONO app.';

try {
    $pdo = getDatabaseConnection();
    $pdo->beginTransaction();

    $faqQuery = $pdo->prepare(
        'SELECT answer
         FROM faqs
         WHERE keyword <> \'\'
           AND LOCATE(LOWER(keyword), LOWER(:message)) > 0
         ORDER BY CHAR_LENGTH(keyword) DESC, id ASC
         LIMIT 1'
    );
    $faqQuery->execute(['message' => $message]);
    $faq = $faqQuery->fetch();
    $answer = $faq === false ? $fallbackAnswer : $faq['answer'];

    $chatInsert = $pdo->prepare(
        'INSERT INTO chats (user_id, message, sender_type)
         VALUES (NULL, :message, :sender_type)'
    );
    $chatInsert->execute([
        'message' => $message,
        'sender_type' => 'user',
    ]);
    $chatInsert->execute([
        'message' => $answer,
        'sender_type' => 'bot',
    ]);

    $pdo->commit();
    respond(200, ['answer' => $answer]);
} catch (RuntimeException $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Chatbot API database error: ' . $exception->getMessage());
    respond(500, ['error' => 'The chatbot is temporarily unavailable. Please try again later.']);
}

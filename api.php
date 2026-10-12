<?php

declare(strict_types=1);

require_once __DIR__ . '/db_connection.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

const GROQ_API_URL = 'https://api.groq.com/openai/v1/chat/completions';
const GROQ_MODEL = 'llama-3.3-70b-versatile';

function respond(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
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

$apiKey = getenv('GROQ_API_KEY');
if ($apiKey === false || trim($apiKey) === '') {
    error_log('Chatbot API error: GROQ_API_KEY is not configured.');
    respond(500, [
        'error' => 'The Groq API key is not configured. Set GROQ_API_KEY in the PHP server environment.',
        'reply' => 'The Groq API key is not configured. Set GROQ_API_KEY in the PHP server environment.',
    ]);
}

if (!function_exists('curl_init')) {
    error_log('Chatbot API error: the PHP cURL extension is not installed.');
    respond(500, [
        'error' => 'The PHP cURL extension is not enabled. Enable it in your PHP configuration and restart the server.',
        'reply' => 'The PHP cURL extension is not enabled. Enable it in your PHP configuration and restart the server.',
    ]);
}

$verifyTls = getenv('GROQ_SSL_VERIFY') !== '0';

$systemPrompt = <<<'PROMPT'
You are a multilingual digital banking assistant for the State Bank of India (SBI).
Support English, Hindi, and Bengali. Detect the language of the user's message and
reply in that same language. Give clear, concise guidance about digital banking.
Never ask for or encourage sharing passwords, PINs, one-time passwords, full card
numbers, or other authentication secrets. Do not claim to access accounts or
perform transactions. For account-specific, urgent, or sensitive issues, direct
the user to an official SBI support channel. Do not invent SBI policies or
account details; be transparent when you cannot verify something.
PROMPT;

$groqPayload = json_encode([
    'model' => GROQ_MODEL,
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $message],
    ],
    'max_tokens' => 1024,
], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

$curl = curl_init(GROQ_API_URL);
if ($curl === false) {
    error_log('Chatbot API error: unable to initialize the Groq request.');
    respond(500, ['error' => 'The chatbot is temporarily unavailable. Please try again later.']);
}

$curlConfigured = curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $groqPayload,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . trim($apiKey),
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_SSL_VERIFYPEER => $verifyTls,
    CURLOPT_SSL_VERIFYHOST => $verifyTls ? 2 : 0,
]);

if (!$curlConfigured) {
    $curlError = curl_error($curl);
    curl_close($curl);
    error_log('Chatbot API error: unable to configure the Groq request. ' . $curlError);
    respond(500, [
        'error' => 'Unable to configure the Groq API request. Check the PHP cURL configuration.',
        'reply' => 'Unable to configure the Groq API request. Check the PHP cURL configuration.',
    ]);
}

$groqResponseBody = curl_exec($curl);
$curlError = curl_error($curl);
$groqStatusCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
curl_close($curl);

if ($groqResponseBody === false) {
    error_log('Chatbot API Groq request failed: ' . $curlError);
    $message = stripos($curlError, 'ssl') !== false || stripos($curlError, 'certificate') !== false
        ? 'Could not verify the Groq API TLS certificate. Configure PHP cURL with a valid CA bundle, or set GROQ_SSL_VERIFY=0 for local testing only.'
        : 'Could not connect to the Groq API. Check the server network connection and try again.';
    respond(502, ['error' => $message, 'reply' => $message]);
}

if ($groqStatusCode < 200 || $groqStatusCode >= 300) {
    $groqErrorMessage = 'No provider error details were returned.';
    try {
        $errorResponse = json_decode($groqResponseBody, true, 512, JSON_THROW_ON_ERROR);
        if (isset($errorResponse['error']['message']) && is_string($errorResponse['error']['message'])) {
            $groqErrorMessage = $errorResponse['error']['message'];
        }
    } catch (JsonException $exception) {
        error_log('Chatbot API error: Groq returned a non-JSON error response.');
    }
    error_log(sprintf(
        'Chatbot API Groq request returned HTTP %d: %s',
        $groqStatusCode,
        $groqErrorMessage
    ));

    $statusCode = $groqStatusCode === 429 ? 503 : 502;
    if ($groqStatusCode === 401 || $groqStatusCode === 403) {
        $message = 'Groq rejected the API key. Check that GROQ_API_KEY is valid and active.';
    } elseif ($groqStatusCode === 429) {
        $message = 'Groq is temporarily rate-limiting requests. Please wait and try again.';
    } elseif ($groqStatusCode === 400) {
        $message = 'Groq could not accept the request. Check the model and request configuration.';
    } elseif ($groqStatusCode >= 500) {
        $message = 'The Groq service is temporarily unavailable. Please try again later.';
    } else {
        $message = 'Groq could not process the request. Check the server log for details.';
    }

    respond($statusCode, ['error' => $message, 'reply' => $message]);
}

try {
    $groqResponse = json_decode($groqResponseBody, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    error_log('Chatbot API error: Groq returned invalid JSON.');
    respond(502, ['error' => 'The assistant returned an invalid response. Please try again later.']);
}

if (
    !is_array($groqResponse)
    || !isset($groqResponse['choices'][0]['message']['content'])
    || !is_string($groqResponse['choices'][0]['message']['content'])
) {
    error_log('Chatbot API error: Groq response did not contain a completion.');
    respond(502, ['error' => 'The assistant returned an invalid response. Please try again later.']);
}

$answer = trim($groqResponse['choices'][0]['message']['content']);
if ($answer === '') {
    error_log('Chatbot API error: Groq returned an empty completion.');
    respond(502, ['error' => 'The assistant returned an empty response. Please try again later.']);
}

try {
    $pdo = getDatabaseConnection();
    $pdo->beginTransaction();

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
    respond(200, ['reply' => $answer, 'answer' => $answer]);
} catch (RuntimeException $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Chatbot API database error: ' . $exception->getMessage());
    respond(500, ['error' => 'The chatbot is temporarily unavailable. Please try again later.']);
}

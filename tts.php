<?php
declare(strict_types=1);

$text = trim((string)($_GET['q'] ?? ''));
$rawLang = strtolower(trim((string)($_GET['lang'] ?? 'en')));

if ($text === '') {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Missing text parameter q']);
    exit;
}

// Normalize language code to Google TTS tl parameter
$tl = 'en';
if (strpos($rawLang, 'bn') === 0) {
    $tl = 'bn';
} elseif (strpos($rawLang, 'hi') === 0) {
    $tl = 'hi';
}

// Cap length per request to keep TTS endpoint reliable
if (mb_strlen($text, 'UTF-8') > 200) {
    $text = mb_substr($text, 0, 200, 'UTF-8');
}

$url = 'https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl='
    . rawurlencode($tl)
    . '&q='
    . rawurlencode($text);

$audioData = false;
$httpCode = 0;

if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Referer: https://translate.google.com/',
            'Accept: audio/mpeg,audio/*;q=0.9,*/*;q=0.8',
        ],
    ]);
    $audioData = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
} else {
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => "User-Agent: Mozilla/5.0\r\nReferer: https://translate.google.com/\r\n",
        ],
    ]);
    $audioData = @file_get_contents($url, false, $ctx);
    $httpCode = $audioData !== false ? 200 : 502;
}

if ($audioData === false || $audioData === '' || $httpCode >= 400) {
    http_response_code(502);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'TTS service unavailable']);
    exit;
}

header('Content-Type: audio/mpeg');
header('Content-Length: ' . strlen($audioData));
header('Cache-Control: public, max-age=86400');
echo $audioData;

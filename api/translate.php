<?php
require_once __DIR__ . '/../config/config.php';
// Google Cloud Translation API integration
header('Content-Type: application/json');

// Get POST data
$text = isset($_POST['text']) ? trim($_POST['text']) : '';
$target = isset($_POST['target_lang']) ? trim($_POST['target_lang']) : 'en';

// Only support en, hi, mr
$supported = ['en', 'hi', 'mr'];
if (!in_array($target, $supported)) {
    $target = 'en';
}

if ($target === 'en' || $text === '') {
    // No translation needed or empty
    echo json_encode(['translatedText' => $text]);
    exit;
}

$apiKey = defined('GOOGLE_TRANSLATE_API_KEY') ? GOOGLE_TRANSLATE_API_KEY : '';
if (!$apiKey) {
    // API key not set, return original text
    echo json_encode(['translatedText' => $text]);
    exit;
}

// Google Cloud Translation API request
$url = 'https://translation.googleapis.com/language/translate/v2';
$data = [
    'q' => $text,
    'target' => $target,
    'format' => 'text',
    'key' => $apiKey
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err || !$response) {
    // Error occurred or no response, return original text
    echo json_encode(['translatedText' => $text]);
    exit;
}

$result = json_decode($response, true);
if (isset($result['data']['translations'][0]['translatedText'])) {
    $translated = $result['data']['translations'][0]['translatedText'];
    echo json_encode(['translatedText' => $translated]);
    exit;
}

// Fallback: return original text
echo json_encode(['translatedText' => $text]);

<?php
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'reply' => "Please login first 🙏"
    ]);
    exit();
}

/* ================= INPUT ================= */
$inputRaw = file_get_contents("php://input");

if (!$inputRaw) {
    echo json_encode([
        'success' => false,
        'reply' => "No input received"
    ]);
    exit();
}

$data = json_decode($inputRaw, true);

if (!isset($data['message'])) {
    echo json_encode([
        'success' => false,
        'reply' => "Invalid request"
    ]);
    exit();
}

$userMessage = trim($data['message']);

if ($userMessage == "") {
    echo json_encode([
        'success' => false,
        'reply' => "Empty message"
    ]);
    exit();
}

/* ================= API KEY (PASTE YOUR KEY HERE) ================= */
$GEMINI_API_KEY =  $_ENV['GEMINI_API_KEY'];

/* ================= VALIDATION ================= */
if ($GEMINI_API_KEY == "" || $GEMINI_API_KEY == $_ENV['GEMINI_API_KEY']) {
    echo json_encode([
        'success' => false,
        'reply' => "API key missing ❌"
    ]);
    exit();
}

/* ================= API URL ================= */
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $GEMINI_API_KEY;

/* ================= PROMPT ================= */
$systemPrompt = "You are a helpful BCA tutor. Explain answers in simple Hinglish, short and clear.";

/* ================= PAYLOAD ================= */
$payload = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" => $systemPrompt . "\n\nUser: " . $userMessage
                ]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 800
    ]
];

/* ================= CURL ================= */
$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

/* ================= ERROR HANDLING ================= */
if ($error) {
    echo json_encode([
        'success' => false,
        'reply' => "CURL Error: " . $error
    ]);
    exit();
}

if ($httpCode != 200) {
    echo json_encode([
        'success' => false,
        'reply' => "HTTP Error: " . $httpCode
    ]);
    exit();
}

$result = json_decode($response, true);

/* ================= RESPONSE CHECK ================= */
if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode([
        'success' => false,
        'reply' => "No valid response from AI"
    ]);
    exit();
}

$reply = $result['candidates'][0]['content']['parts'][0]['text'];

/* ================= OUTPUT ================= */
echo json_encode([
    'success' => true,
    'reply' => $reply
]);
?>
<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();
// Set headers to guarantee a clean UTF-8 JSON response output matrix
header('Content-Type: application/json; charset=utf-8');

// Production Error Masking: Log problems silently without generating raw HTML output breaks
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Initialize session framework safely if omitted by core routing architecture
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Universal JSON error exit handler with automated system logging.
 */
function terminateWithError($logMessage, $userReply) {
    error_log("[AI Tutor System] " . $logMessage);
    echo json_encode([
        'success' => false,
        'reply' => $userReply
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

/* ================= SECURITY VALIDATION MATRIX ================= */
if (!isset($_SESSION['user_id'])) {
    terminateWithError(
        "Unauthorized endpoint access attempt. Missing session user_id.",
        "Please login first 🙏"
    );
}

/* ================= CONFIGURATION & INITIALIZATION ================= */
// Target Key Variable Assignment Block
$GEMINI_API_KEY =  $_ENV['GEMINI_API_KEY'];;

// Strict validation check requested: check only if the variable is empty
if (empty($GEMINI_API_KEY)) {
    terminateWithError(
        "Gemini API execution stopped. Configuration key placeholder string evaluates to empty.",
        "Invalid API key"
    );
}

// Endpoint URI Target Composition
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $GEMINI_API_KEY;

/* ================= INPUT EXTRACTION & VALIDATION ================= */
$inputRaw = file_get_contents('php://input');

if ($inputRaw === false || trim($inputRaw) === '') {
    terminateWithError(
        "Empty raw input container captured at php://input stream.",
        "No input received"
    );
}

$data = json_decode($inputRaw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    terminateWithError(
        "Failed decoding structural payload string. Code: " . json_last_error_msg(),
        "Invalid JSON"
    );
}

if (!$data || !isset($data['message'])) {
    terminateWithError(
        "Invalid incoming dictionary tree context. Missing required 'message' routing keys.",
        "Invalid request format"
    );
}

$userMessage = trim((string)$data['message']);

if ($userMessage === '') {
    terminateWithError(
        "Cleaned text string evaluated to zero-length metrics after handling whitespace isolation.",
        "Empty message"
    );
}

/* ================= PAYLOAD MATRIX COMPOSITION ================= */
// Exact explicit instructions provided by your setup criteria
$systemInstruction = "You are an AI Tutor for a BCA Quiz System.\n" .
                     "Explain answers in simple Hinglish.\n" .
                     "Keep answers short, clear and student friendly.";

$payload = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" => $systemInstruction . "\n\nStudent Question: " . $userMessage
                ]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 800
    ]
];

/* ================= NETWORK ENGINE LAYER (CURL) ================= */
$ch = curl_init($apiUrl);

$encodedPayload = json_encode($payload);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json; charset=utf-8'
]);

// Crucial connection and operational processing timeouts mandated by production safety
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Secure Peer Verification parameters explicitly required
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

/* ================= ENGINE FAULT & EXCEPTION ANALYSIS ================= */
if ($curlError) {
    terminateWithError(
        "cURL hardware transport runtime exception thrown: " . $curlError,
        "Timeout errors"
    );
}

$result = json_decode($response, true);
$isJsonValid = (json_last_error() === JSON_ERROR_NONE);

// Intercept non-200 transaction errors and parse response anomalies safely
if ($httpCode !== 200) {
    $errorMessage = "HTTP Remote network gateway anomaly noted. Response Code: " . $httpCode;
    
    if ($isJsonValid && isset($result['error']['message'])) {
        // Appends the verified error string directly returned from the Gemini cluster
        $errorMessage .= " | API Message: " . $result['error']['message'];
        $userResponse = "Gemini API Error: " . $result['error']['message'];
    } else {
        $errorMessage .= " | Body: " . $response;
        $userResponse = "HTTP errors. Server encountered code " . $httpCode;
    }
    
    terminateWithError($errorMessage, $userResponse);
}

if (!$isJsonValid) {
    terminateWithError(
        "Unable to compile target API return string. Response context contains flawed formatting anomalies. Output: " . $response,
        "Invalid Gemini response"
    );
}

/* ================= COMPONENT DESERIALIZATION & EXECUTION ================= */
// Deep validation matrix checks: candidates -> content -> parts -> text safely mapped
if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    terminateWithError(
        "Structural layout change caught on Gemini interface layer fields. Matrix path unresolvable. Data: " . $response,
        "No valid response received from AI"
    );
}

$aiReply = trim((string)$result['candidates'][0]['content']['parts'][0]['text']);

if ($aiReply === '') {
    terminateWithError(
        "Gemini text resolution completed successfully but isolated text payload element evaluates to null/empty metrics.",
        "No valid response received from AI"
    );
}

/* ================= UNIFORM DATA PAYLOAD RESPONSE ================= */
echo json_encode([
    'success' => true,
    'reply' => $aiReply
], JSON_UNESCAPED_UNICODE);
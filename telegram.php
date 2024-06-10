<?php

// Security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
header('Content-Type: application/json');
header('X-Robots-Tag: noindex, nofollow', true);

// Validate HTTP method
$allowed_methods = ['GET', 'POST'];
if (!in_array($_SERVER['REQUEST_METHOD'], $allowed_methods)) {
    http_response_code(405);
    echo json_encode(["error" => "Only GET and POST requests are allowed."]);
    exit();
}

// Function to validate URL
function validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) && strpos($url, 'api.telegram.org') !== false;
}

// Retrieve the API URL from the request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $api_url = $_GET['url'] ?? null;
} else {
    $input_data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON in request body."]);
        exit();
    }
    $api_url = $input_data['url'] ?? null;
}

if (!$api_url || !validate_url($api_url)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing API URL."]);
    exit();
}

// Ensure the URL is not too long
if (strlen($api_url) > 1000) {
    http_response_code(400);
    echo json_encode(["error" => "URL length exceeds the maximum allowed limit."]);
    exit();
}

// Parse and validate the URL
$parsed_url = parse_url($api_url);
if (!isset($parsed_url['scheme'], $parsed_url['host']) || !in_array($parsed_url['scheme'], ['http', 'https'])) {
    http_response_code(400);
    echo json_encode(["error" => "Only HTTP and HTTPS protocols are allowed."]);
    exit();
}

// Prevent directory traversal
if (strpos($api_url, '..') !== false) {
    http_response_code(400);
    echo json_encode(["error" => "Directory traversal detected."]);
    exit();
}

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // Force IPv4
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set timeout to 30 seconds

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postFields = json_encode($input_data['data'] ?? []);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON in 'data' field."]);
        exit();
    }
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
}

// Execute cURL request
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    http_response_code(500);
    echo json_encode(["error" => "cURL error: " . $curl_error]);
    exit();
}

// Set the response HTTP status code
http_response_code($http_status);

// Output the response from the Telegram API
echo $response;

?>
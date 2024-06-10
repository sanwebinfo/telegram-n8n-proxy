<?php

// Set appropriate security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
header('Content-Type: application/json');
header('X-Robots-Tag: noindex, nofollow', true);

// Set the base URL of the Telegram API
$base_url = 'https://api.telegram.org';

// Check if it's a GET request for webhook setup
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(["message" => "This is a webhook setup endpoint."]);
    exit();
}

// Get the POST data from the request
$post_data = file_get_contents('php://input');

//if ($post_data === false || empty($post_data)) {
//    http_response_code(400);
 //   echo json_encode(["error" => "No or empty POST data received."]);
 //   exit();
//}

// Get the API endpoint URL from the request
$endpoint_url = $_GET['url'] ?? '';
if (!$endpoint_url) {
    http_response_code(400);
    echo json_encode(["error" => "API endpoint URL not provided."]);
    exit();
}

// Check if the provided URL is a valid Telegram API endpoint
if (strpos($endpoint_url, $base_url) !== 0) {
    http_response_code(403);
    echo json_encode(["error" => "Access to the provided URL is not allowed."]);
    exit();
}

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt_array($ch, [
    CURLOPT_URL => $endpoint_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $post_data,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
]);

// Execute cURL request
$response = curl_exec($ch);
if ($response === false) {
    http_response_code(500);
    echo json_encode(["error" => "cURL error: " . curl_error($ch)]);
    exit();
}

// Get HTTP status code of the response
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Set the HTTP status code of the response
http_response_code($http_status);

// Output the response from the Telegram API
echo $response;

?>
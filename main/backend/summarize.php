<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if the request is POST and has content
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['content'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

// Get the content to summarize
$content = $_POST['content'];

// Your Gemini API key - replace with your actual API key
$apiKey = "AIzaSyDT642gkqTNO45h6ZP-hRufEmzGID3Sc7A";
// Updated API URL to use the gemini-2.0-flash model
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

// Prepare the request data
$data = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" => "Please provide a concise summary of the following text in 3-5 bullet points:\n\n" . $content
                ]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.2,
        "topK" => 40,
        "topP" => 0.95,
        "maxOutputTokens" => 1024
    ]
];

// Initialize cURL session
$ch = curl_init($apiUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Add this line to disable SSL verification (for testing only)
curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging

// Create a file handle for the verbose information
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

// Execute the request
$response = curl_exec($ch);

// Get HTTP status code
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for errors
if (curl_errno($ch)) {
    // Get verbose information
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    
    http_response_code(500);
    echo json_encode([
        'error' => 'API request failed: ' . curl_error($ch),
        'verbose' => $verboseLog
    ]);
    exit();
}

// Close cURL session
curl_close($ch);

// If HTTP status code is not 200, return error
if ($httpCode != 200) {
    http_response_code($httpCode);
    echo json_encode([
        'error' => 'API returned error code: ' . $httpCode,
        'response' => $response
    ]);
    exit();
}

// Decode the response
$result = json_decode($response, true);

// Extract the summary from the response
if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $summary = $result['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(['summary' => $summary]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to generate summary',
        'response' => $result,
        'raw_response' => $response
    ]);
}
?>
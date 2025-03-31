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
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['content']) || empty($_POST['style'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

// Get the content to rewrite and the style
$content = $_POST['content'];
$style = $_POST['style'];

// Map style to prompt instructions
$stylePrompts = [
    'professional' => 'Rewrite the following text in a professional, formal style suitable for business or academic contexts:',
    'creative' => 'Rewrite the following text in a creative, engaging style with colorful language and imagery:',
    'simple' => 'Rewrite the following text in a simple, clear style that is easy to understand with simpler vocabulary:',
    'persuasive' => 'Rewrite the following text in a persuasive style designed to convince and influence the reader:'
];

// Get the appropriate prompt for the selected style
$stylePrompt = isset($stylePrompts[$style]) ? $stylePrompts[$style] : $stylePrompts['professional'];

// Your Gemini API key - same as in summarize.php
$apiKey = "AIzaSyDT642gkqTNO45h6ZP-hRufEmzGID3Sc7A";
// Updated API URL to use the gemini-2.0-flash model
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

// Prepare the request data
$data = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" => $stylePrompt . "\n\n" . $content . "\n\nMaintain the original meaning but change the writing style. Keep approximately the same length."
                ]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "topK" => 40,
        "topP" => 0.95,
        "maxOutputTokens" => 2048
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

// Execute the request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'API request failed: ' . curl_error($ch)]);
    exit();
}

// Close cURL session
curl_close($ch);

// Decode the response
$responseData = json_decode($response, true);

// Check if the response contains the expected data
if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $rewrittenText = $responseData['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(['rewritten' => $rewrittenText]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Unexpected API response format',
        'response' => $responseData
    ]);
}
?>
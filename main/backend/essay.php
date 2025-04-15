<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loadEnv($path) {
    if (!file_exists($path)) return;
  
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
  }
  
  loadEnv('../../.env');
  

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Check if title is provided
if (!isset($_POST['title']) || empty($_POST['title'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No title provided']);
    exit();
}

$title = $_POST['title'];

// Function to generate an essay using Gemini API
function generateEssayWithGemini($title) {
    // Your Gemini API key - you should store this in a more secure way
    $apiKey = getenv("API_KEY"); // Replace with your actual API key
    
    // Gemini API endpoint
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;
    
    // Prepare the prompt for Gemini
    $prompt = "Write a well-structured, informative 250-word essay about the following topic: " . $title . ". The essay should be academic in tone, include an introduction, body paragraphs, and a conclusion.";
    
    // Prepare the request data
    $data = [
        "contents" => [
            [
                "parts" => [
                    [
                        "text" => $prompt
                    ]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 800,
            "topP" => 0.8,
            "topK" => 40
        ]
    ];
    
    // Initialize cURL session
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // Execute cURL request
    $response = curl_exec($ch);
    
    // Check for errors
    if (curl_errno($ch)) {
        curl_close($ch);
        return "Error generating essay: " . curl_error($ch);
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Parse the response
    $responseData = json_decode($response, true);
    
    // Extract the generated text
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        return $responseData['candidates'][0]['content']['parts'][0]['text'];
    } else {
        return "Sorry, unable to generate an essay at this time. Please try again later.";
    }
}

// Generate the essay using Gemini
$essay = generateEssayWithGemini($title);

// Return the essay as JSON
header('Content-Type: application/json');
echo json_encode(['essay' => $essay]);
?>
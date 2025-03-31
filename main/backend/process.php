<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "notesdb";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    
    // Insert the note
    $sql = "INSERT INTO notes (user_id, title, content, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $title, $content);
    
    if ($stmt->execute()) {
        $note_id = $conn->insert_id;
        
        // Handle file uploads
        if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'][0])) {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Process each uploaded file
            $files = $_FILES['attachment'];
            $file_count = count($files['name']);
            
            for ($i = 0; $i < $file_count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $files['tmp_name'][$i];
                    $name = $files['name'][$i];
                    $type = $files['type'][$i];
                    $size = $files['size'][$i];
                    
                    // Generate a unique filename to prevent overwriting
                    $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                    $unique_filename = uniqid('note_' . $note_id . '_') . '.' . $file_extension;
                    $file_path = $upload_dir . $unique_filename;
                    
                    // Move the uploaded file to the server
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        // Insert attachment info into the database
                        $attach_sql = "INSERT INTO attachments (note_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)";
                        $attach_stmt = $conn->prepare($attach_sql);
                        $attach_stmt->bind_param("isssi", $note_id, $name, $file_path, $type, $size);
                        $attach_stmt->execute();
                    }
                }
            }
        }
        
        header("Location: ../display.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

$conn->close();
?>

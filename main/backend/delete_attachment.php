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

if (isset($_GET['id']) && isset($_GET['note_id'])) {
    $attachment_id = (int)$_GET['id'];
    $note_id = (int)$_GET['note_id'];
    $user_id = $_SESSION['user_id'];
    
    // First verify that the attachment belongs to a note owned by the current user
    $verify_sql = "SELECT a.* FROM attachments a 
                  JOIN notes n ON a.note_id = n.id 
                  WHERE a.id = ? AND n.user_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $attachment_id, $user_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $attachment = $result->fetch_assoc();
        $file_path = $attachment['file_path'];
        
        // Delete the file from the server
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Delete the attachment record from the database
        $delete_sql = "DELETE FROM attachments WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $attachment_id);
        $delete_stmt->execute();
    }
    
    // Redirect back to the edit page
    header("Location: edit.php?id=" . $note_id);
    exit();
} else {
    header("Location: ../display.php");
    exit();
}
?>
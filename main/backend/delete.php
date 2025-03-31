<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "notesdb";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get logged-in user's ID

// Check if note_id is set
if (isset($_POST['note_id'])) {
    $note_id = $_POST['note_id'];

    // Delete the note
    $sql = "DELETE FROM notes WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $note_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Redirect to the display page after successful deletion
        header("Location: ../display.php?message=Note deleted successfully");
        exit();
    } else {
        // Redirect with an error message if the deletion failed
        header("Location: ../display.php?error=Failed to delete note");
        exit();
    }
} else {
    // Redirect with an error message if note_id is not provided
    header("Location: ../display.php?error=Invalid request");
    exit();
}


?>
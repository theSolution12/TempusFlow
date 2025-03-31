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
    $event_id = (int)$_POST['event_id'];
    $user_id = $_SESSION['user_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $event_date = $conn->real_escape_string($_POST['event_date']);
    $event_time = $conn->real_escape_string($_POST['event_time']);
    $event_end_time = $conn->real_escape_string($_POST['event_end_time'] ?? '');
    $location = $conn->real_escape_string($_POST['location'] ?? '');

    $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, event_end_time = ?, location = ? 
            WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssii", $title, $description, $event_date, $event_time, $event_end_time, $location, $event_id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: ../display.php?filter=events");
        exit();
    } else {
        echo "Error updating event: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>
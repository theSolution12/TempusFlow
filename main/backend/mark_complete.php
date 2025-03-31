<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;

    if ($task_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit();
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "notesdb";

    // Connect to MySQL
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }

    // Mark task as complete
    $sql = "UPDATE tasks SET status = 'completed' WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $task_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error marking task as complete']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
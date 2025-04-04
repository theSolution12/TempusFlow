<?php
session_start();

$serverName = "localhost"; 
$userName = "root";
$password = "";
$dbName = "notesdb";


$conn = mysqli_connect($serverName, $userName, $password, $dbName);

if (!$conn) {
    $_SESSION['error'] = "Database connection failed: " . mysqli_connect_error();
    header("location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    

    $token = $_POST['token'];
    $password = $_POST['password'];

    
    $query = "SELECT email FROM users WHERE reset_token = '$token' AND token_expiry > NOW()";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $email = $row['email'];

        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        
        $updateQuery = "UPDATE users SET password = '$hashedPassword', reset_token = NULL, token_expiry = NULL WHERE email = '$email'";
        if (mysqli_query($conn, $updateQuery)) {
            $_SESSION['success'] = "Password reset successfully! Please log in.";
            header("location: ../login.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to reset password. Try again.";
        }
    } else {
        $_SESSION['error'] = "Invalid or expired token.";
    }
}

header("location: ../login.php");
exit();
?>
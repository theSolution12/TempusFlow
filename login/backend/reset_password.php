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

    
    $query = mysqli_prepare($conn, "SELECT email FROM users WHERE reset_token = ? AND token_expiry > NOW()");
    mysqli_stmt_bind_param($query, "s", $token);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $email = $row['email'];

        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        
        $updateQuery = mysqli_prepare($conn, "UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?");
        mysqli_stmt_bind_param($updateQuery, "ss", $hashedPassword, $email);
        
        
        if (mysqli_stmt_execute($updateQuery)) {
            $_SESSION['success'] = "Password reset successfully! Please log in.";
            unset($_SESSION['error']);
            header("location: ../login.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to reset password. Try again.";
            unset($_SESSION['success']);
        }
    } else {
        $_SESSION['error'] = "Invalid or expired token.";
        unset($_SESSION['success']);
    }
}

header("location: ../login.php");
exit();
?>
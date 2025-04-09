<?php
session_start();

$serverName = "localhost";
$userName = "root";
$password = "";
$conn = mysqli_connect($serverName, $userName, $password);

if (mysqli_connect_errno()) {
    $_SESSION['error'] = "Database connection failed!";
    header("location: ../login.php");
    exit();
}

mysqli_select_db($conn, "notesdb");

$email = trim($_POST['email']);
$pass = trim($_POST['password']);

$checkQuery = mysqli_prepare($conn, "SELECT id, username, email, password FROM users WHERE email = ?");
mysqli_stmt_bind_param($checkQuery, "s", $email);
mysqli_stmt_execute($checkQuery);
$checkResult = mysqli_stmt_get_result($checkQuery);


if (mysqli_num_rows($checkResult) == 0) {
    $_SESSION['error'] = "Email not found!";
    unset($_SESSION['success']);
    header("location: ../login.php");
    exit();
} else {
    $row = mysqli_fetch_assoc($checkResult);
    $hashedPass = $row['password'];

    
    if (password_verify($pass, $hashedPass)) {
        unset($_SESSION['error']);
        unset($_SESSION['success']);
        $_SESSION['user_id'] = $row['id']; 
        $_SESSION['username'] = $row['username']; 
        // $_SESSION['success'] = "Login successful!";
        header("location: ../../main/main.php");
        exit();
    } else {
        $_SESSION['error'] = "Incorrect password!";
        unset($_SESSION['success']);
        header("location: ../login.php");
        exit();
    }
}
?>

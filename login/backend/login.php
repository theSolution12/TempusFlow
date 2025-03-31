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

if (empty($email) || empty($pass)) {
    $_SESSION['error'] = "All fields are required!";
    header("location: ../login.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format!";
    header("location: ../login.php");
    exit();
}

$checkQuery = mysqli_prepare($conn, "SELECT id, username, email, password FROM users WHERE email = ?");
mysqli_stmt_bind_param($checkQuery, "s", $email);
mysqli_stmt_execute($checkQuery);
$checkResult = mysqli_stmt_get_result($checkQuery);

if (mysqli_num_rows($checkResult) == 0) {
    $_SESSION['error'] = "Email not found!";
    header("location: ../login.php");
    exit();
} else {
    $row = mysqli_fetch_assoc($checkResult);
    $hashedPass = $row['password'];

    if (password_verify($pass, $hashedPass)) {
        $_SESSION['error'] = ""; // Clear error on successful login
        $_SESSION['user_id'] = $row['id']; // Set user_id in session
        $_SESSION['username'] = $row['username']; // Set username in session
        header("location: ../../main/display.php");
        exit();
    } else {
        $_SESSION['error'] = "Incorrect password!";
        header("location: ../login.php");
        exit();
    }
}
?>

<?php
session_start();

$serverName = "localhost";
$userName = "root";
$password = "";

$conn = mysqli_connect($serverName, $userName, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_select_db($conn, "notesdb");

$userName = trim($_POST["username"]);
$email = trim($_POST['email']);
$pass = trim($_POST['password']);


$hashedPass = password_hash($pass, PASSWORD_BCRYPT);


$checkQuery = mysqli_prepare($conn, "SELECT email FROM users WHERE email = ?");
mysqli_stmt_bind_param($checkQuery, "s", $email);
mysqli_stmt_execute($checkQuery);
$checkResult = mysqli_stmt_get_result($checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    $_SESSION['error'] = "Email already exists!";
    unset($_SESSION['success']);
    header("location: ../register.php");
    exit();
}

$checkQuery = mysqli_prepare($conn, "SELECT email FROM users WHERE username = ?");
mysqli_stmt_bind_param($checkQuery, "s", $userName);
mysqli_stmt_execute($checkQuery);
$checkResult = mysqli_stmt_get_result($checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    $_SESSION['error'] = "Username already exists!";
    unset($_SESSION['success']);
    header("location: ../register.php");
    exit();
}

$query = mysqli_prepare($conn, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($query, "sss", $userName, $email, $hashedPass);
$result = mysqli_stmt_execute($query);

if ($result) {
    $_SESSION['success'] = "Account created successfully!";
    unset($_SESSION['error']);
    header("location: ../login.php");
    exit();
} else {
    die("Error: " . mysqli_stmt_error($query));
}
?>

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
$confirmPass = trim($_POST['confirmPassword']);

if (empty($email) || empty($pass) || empty($confirmPass || empty($userName))) {
    $_SESSION['error'] = "All fields are required!";
    header("location: ../register.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format!";
    header("location: ../register.php");
    exit();
}

if ($pass !== $confirmPass) {
    $_SESSION['error'] = "Passwords do not match!";
    header("location: ../register.php");
    exit();
}

if (strlen($pass) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters!";
    header("location: ../register.php");
    exit();
}

// Hash the password before storing
$hashedPass = password_hash($pass, PASSWORD_BCRYPT);

// Check if the email already exists
$checkQuery = mysqli_prepare($conn, "SELECT email FROM users WHERE email = ?");
mysqli_stmt_bind_param($checkQuery, "s", $email);
mysqli_stmt_execute($checkQuery);
$checkResult = mysqli_stmt_get_result($checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    $_SESSION['error'] = "Email already exists!";
    header("location: ../register.php");
    exit();
}

// Insert new user
$query = mysqli_prepare($conn, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($query, "sss", $userName, $email, $hashedPass);
$result = mysqli_stmt_execute($query);

if ($result) {
    $_SESSION['success'] = "Account created successfully!";
    header("location: ../login.php");
    exit();
} else {
    die("Error: " . mysqli_stmt_error($query));
}
?>

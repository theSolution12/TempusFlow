<?php
session_start();

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function loadEnv($path) {
    if (!file_exists($path)) return;
  
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}
  
loadEnv('../../.env');

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
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        header("location: ../forgot_password.php");
        exit();
    }

    if (mysqli_num_rows($result) > 0) {
       
        $token = bin2hex(random_bytes(50));

        
        $updateQuery = "UPDATE users SET reset_token = '$token', token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = '$email'";

        if (mysqli_query($conn, $updateQuery)) {
            
            $resetLink = "http://localhost/int220/login/reset_password.php?token=$token";

            
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = getenv('EMAIL_USERNAME');
                $mail->Password = getenv('EMAIL_PASSWORD');
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
            
                $mail->setFrom('parthpatidar127@gmail.com', 'TempusFlow');
                $mail->addAddress($email);
                $mail->Subject = "Password Reset Request";
                $mail->Body = "Click the link below to reset your password:\n$resetLink";
            
                $mail->send();
                $_SESSION['success'] = "Password reset link has been sent!";
                unset($_SESSION['error']);
            } catch (Exception $e) {
                $_SESSION['error'] = "Mailer Error: " . $mail->ErrorInfo;
                unset($_SESSION['success']);
            }
        } else {
            $_SESSION['error'] = "Failed to generate reset token.";
            unset($_SESSION['success']);
        }
    } else {
        $_SESSION['error'] = "No account found with that email.";
        unset($_SESSION['success']);
    }

    header("location: ../forgot_password.php");
    exit();
}
?>

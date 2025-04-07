<?php
session_start();

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$serverName = "localhost";
$userName = "root";
$password = "";
$dbName = "notesdb";

// Database Connection
$conn = mysqli_connect($serverName, $userName, $password, $dbName);

if (!$conn) {
    $_SESSION['error'] = "Database connection failed: " . mysqli_connect_error();
    header("location: ../login.php");
    exit();
}

// Handle POST Request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if email exists
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        header("location: ../forgot_password.php");
        exit();
    }

    if (mysqli_num_rows($result) > 0) {
        // Generate secure reset token
        $token = bin2hex(random_bytes(50));

        // Update user record with reset token and expiry time
        $updateQuery = "UPDATE users SET reset_token = '$token', token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = '$email'";

        if (mysqli_query($conn, $updateQuery)) {
            // Construct reset link
            $resetLink = "http://localhost/int220/login/reset_password.php?token=$token";

            // Email content
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'parthpatidar127@gmail.com';
                $mail->Password = 'shap werl dvcy llpf';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
            
                $mail->setFrom('parthpatidar127@gmail.com', 'Your Website');
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

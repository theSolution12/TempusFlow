<?php
session_start();

require '../../login/backend/PHPMailer/src/PHPMailer.php';
require '../../login/backend/PHPMailer/src/Exception.php';
require '../../login/backend/PHPMailer/src/SMTP.php';

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = htmlspecialchars($_POST["name"]);
  $email = htmlspecialchars($_POST["email"]);
  $message = htmlspecialchars($_POST["message"]);

  $mail = new PHPMailer(true);

  try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Host = 'smtp.gmail.com';
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('EMAIL_USERNAME');
    $mail->Password = getenv('EMAIL_PASSWORD');
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom(getenv('EMAIL_USERNAME'), 'TempusFlow');
    $mail->addAddress(getenv('EMAIL_USERNAME'), 'Admin');
    $mail->Subject = "New contact request from $email";
    $mail->Body = "$message\n\nFrom: $name\nEmail: $email";
    $mail->isHTML(true);

    $mail->send();
    $_SESSION['success'] = "Contact form submitted successfully!";
    header("Location: ../contact.php");
    unset($_SESSION['error']);
  } catch (Exception $e) {
    $_SESSION["error"] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    header("Location: ../contact.php");
  }
} else {
  $_SESSION["error"] = "Invalid request method.";
  header("Location: ../contact.php");
}
?>
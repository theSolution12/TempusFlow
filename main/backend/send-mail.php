<?php
session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = htmlspecialchars($_POST["name"]);
  $email = htmlspecialchars($_POST["email"]);
  $message = htmlspecialchars($_POST["message"]);

  // Email destination
  $to = "parthpatidar127@gmail.com";
  $subject = "New Contact Form Message from $name";

  $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
  $headers = "From: $email" . "\r\n" .
             "Reply-To: $email" . "\r\n";

  if (mail($to, $subject, $body, $headers)) {
    $_SESSION["success"] = "Message sent successfully!";
    header("Location: ../contact.php"); // Redirect to the contact page or a thank you page
  } else {
    $_SESSION["error"] = "Failed to send message. Please try again later.";
    header("Location: ../contact.php"); // Redirect back to the contact page
  }
} else {
    $_SESSION["error"] = "Invalid request method.";
    header("Location: ../contact.php"); // Redirect back to the contact page
}
?>

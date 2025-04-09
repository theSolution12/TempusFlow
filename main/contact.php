<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="min-h-screen bg-gray-50 font-sans">

    <div class="flex flex-col items-center justify-center py-16 px-6">
      <h1 class="text-4xl font-bold mb-4 text-gray-800">Contact Us</h1>
      <p class="text-gray-600 mb-10 max-w-lg text-center">
        Got a question, suggestion, or just want to say hello? Fill out the form below and we'll get back to you!
      </p>

      <form action="./backend/send-mail.php" method="POST" class="w-full max-w-lg bg-white p-8 rounded-lg shadow-md space-y-6">
      <?php
          if (isset($_SESSION['error'])) {
              echo '<p class="text-red-500 text-center font-semibold bg-red-200 p-2 rounded mb-4">' . $_SESSION['error'] . '</p>';
              unset($_SESSION['error']);
          } else if (isset($_SESSION['success'])) {
              echo '<p class="text-green-500 text-center font-semibold bg-green-200 p-2 rounded mb-4">' . $_SESSION['success'] . '</p>';
              unset($_SESSION['success']);
          }
        ?>
        <div>
          <label for="name" class="block text-gray-700 font-medium mb-2">Your Name</label>
          <input
            type="text"
            id="name"
            name="name"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <div>
          <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
          <input
            type="email"
            id="email"
            name="email"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <div>
          <label for="message" class="block text-gray-700 font-medium mb-2">Your Message</label>
          <textarea
            id="message"
            name="message"
            rows="5"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          ></textarea>
        </div>

        <div class="text-center">
          <button
            type="submit"
            class="bg-blue-700 text-white px-6 py-3 rounded-full font-semibold hover:bg-blue-800 transition"
          >
            Send Message
          </button>
        </div>
      </form>
    </div>

  </body>
</html>

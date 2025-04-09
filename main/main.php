<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Landing Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="h-screen w-screen font-sans">

    <div class="flex h-full">
      <!-- 80% Left Section -->
      <div
        class="w-full bg-cover bg-center flex flex-col px-10 py-6"
        style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('../assets/login-bg.png');"
      >
        <!-- Navbar -->
        <nav class="flex items-center justify-between mb-12 text-white">
          <div class="text-2xl font-bold">TempusFlow</div>
          <ul class="flex space-x-6 text-lg font-medium">
            <li><a href="#" class="hover:text-gray-300">Home</a></li>
            <li><a href="./display.php" class="hover:text-gray-300">Tool</a></li>
            <li><a href="./contact.php" class="hover:text-gray-300">Contact</a></li>
          </ul>
          <?php
            if (!isset($_SESSION['user_id'])) {
              echo '<a href="../login/login.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">Login</a>' ;
            }
            else{
              echo '<a href="../login/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition duration-300">Logout</a>' ;
            }
          ?>
          
        </nav>

        <!-- Hero Content -->
        <div class="flex-1 flex flex-col gap-10 items-center justify-center">
          <div class="text-white text-center max-w-xl">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">Welcome to Our Platform</h1>
            <p class="text-lg md:text-xl text-gray-200">
              Discover tools that boost your productivity and transform your workflow.
            </p>
          </div>
          <a href="./display.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">Go to Tool</a>
        </div>
      </div>

  </body>
</html>

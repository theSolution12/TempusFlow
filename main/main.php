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
    <!-- Full Width Section -->
    <div class="w-full bg-cover bg-center flex flex-col px-10 py-6"
      style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../assets/login-bg.png');">
      <!-- Navbar -->
      <nav class="flex items-center justify-between mb-8 text-white">
        <!-- Left: Logo -->
        <div class="flex items-center gap-3">
        <img src="../assets/LOGO.png" width="100px" alt="TempusFlow Logo" class="object-contain" />
        </div>

        <!-- Middle: Nav list -->
        <div class="flex items-center gap-6">
          <ul class="flex space-x-6 text-lg font-medium">
            <li><a href="#" class="hover:text-gray-300">Home</a></li>
            <li><a href="./display.php" class="hover:text-gray-300">Tool</a></li>
            <li><a href="./contact.php" class="hover:text-gray-300">Contact</a></li>
          </ul>
        </div>

        <!-- Right: Login/Logout -->
        <div>
          <?php
          if (!isset($_SESSION['user_id'])) {
            echo '<a href="../login/login.php" class="bg-black/20 backdrop:blur-lg text-white px-6 py-3 border border-gray-400 rounded-full hover:bg-black/30 transition duration-300">Login</a>';
          } else {
            echo '<a href="../login/logout.php" class="bg-black/20 backdrop:blur-lg text-white px-6 py-3 border border-red-400 rounded-full hover:bg-black/30 transition duration-300">Logout</a>';
          }
          ?>
        </div>
      </nav>

      <!-- Hero Content -->
      <div class="flex-1 flex flex-col gap-10 items-center justify-center">
        <div class="text-white text-center max-w-xl">
          <h1 class="text-4xl md:text-6xl font-bold mb-4">Welcome to Our Platform</h1>
          <p class="text-lg md:text-xl text-gray-200">
            Discover tools that boost your productivity and transform your workflow.
          </p>
        </div>
        <a href="./display.php"
          class="relative inline-flex items-center justify-center px-10 py-3 rounded-full bg-gradient-to-br from-indigo-700 via-purple-800 to-indigo-900 text-white text-lg font-semibold shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300">
          <span
            class="absolute inset-0 rounded-full bg-white opacity-5 hover:opacity-10 transition duration-300"></span>
          <span class="relative z-10">Launch Tool</span>
        </a>

      </div>
    </div>
  </div>

</body>

</html>
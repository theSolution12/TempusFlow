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
        class="w-4/5 bg-cover bg-center flex flex-col px-10 py-6"
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
        </nav>

        <!-- Hero Content -->
        <div class="flex-1 flex items-center justify-center">
          <div class="text-white text-center max-w-xl">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">Welcome to Our Platform</h1>
            <p class="text-lg md:text-xl text-gray-200">
              Discover tools that boost your productivity and transform your workflow.
            </p>
          </div>
        </div>
      </div>

      <!-- 20% Right Section -->
      <div class="w-1/5 bg-white flex flex-col gap-4 items-center justify-center">
        <a
          href="./display.php"
          class="bg-blue-700 text-white px-6 py-3 rounded-full font-semibold text-lg hover:bg-blue-800 transition"
        >
          Go to Tool
        </a>
      </div>
    </div>

  </body>
</html>

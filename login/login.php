<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" crossorigin="" />
  <style>
    .cardImage {
      background-image: url('../assets/login-bg.png');
      background-size: cover;
      background-position: center;
    }
  </style>
</head>

<body class="w-screen h-screen flex items-center justify-center">

  <div class="w-full max-w-4xl h-5/6 bg-white shadow-lg rounded-lg overflow-hidden flex">

    <div class="w-1/2 cardImage flex items-center justify-center">
      <div class="bg-black/40 w-full h-full flex items-center justify-center">
        <img src="../assets/LOGO.png" width="400px" alt="" class="object-contain" />
      </div>
    </div>


    <div class="w-1/2 flex items-center justify-center p-8 bg-gray-100">
      <form action="./backend/login.php" method="post" class="w-full max-w-sm" onsubmit="return validateForm()">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

        <?php
        if (isset($_SESSION['error'])) {
          echo '<p class="text-red-500 text-center font-semibold bg-red-200 p-2 rounded mb-4">' . $_SESSION['error'] . '</p>';
          unset($_SESSION['error']);
        } else if (isset($_SESSION['success'])) {
          echo '<p class="text-green-500 text-center font-semibold bg-green-200 p-2 rounded mb-4">' . $_SESSION['success'] . '</p>';
          unset($_SESSION['success']);
        }
        ?>

        <div class="mb-4">
          <label class="block text-gray-700 mb-2" for="email">Email</label>
          <input class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            type="email" id="email" name="email" placeholder="you@example.com" required />
          <span id="emailError" class="text-red-500 text-sm hidden"></span>
        </div>

        <div class="mb-6">
          <label class="block text-gray-700 mb-2" for="password">Password</label>
          <input class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            type="password" id="password" name="password" placeholder="••••••••" required />
          <span id="passwordError" class="text-red-500 text-sm hidden"></span>
        </div>

        <div class="mb-4 flex items-center justify-between text-sm">
          <a href="register.php" class="text-blue-600 hover:underline">Register</a>
          <a href="forgot_password.php" class="text-blue-600 hover:underline">Forgot Password?</a>
        </div>

        <input type="submit"
          class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition cursor-pointer"
          value="Submit" />
      </form>
    </div>

  </div>

  <script>
    function validateForm() {
      let email = document.getElementById("email").value.trim();
      let password = document.getElementById("password").value.trim();
      let emailError = document.getElementById("emailError");
      let passwordError = document.getElementById("passwordError");
      let isValid = true;

      emailError.innerText = "";
      emailError.classList.add("hidden");
      passwordError.innerText = "";
      passwordError.classList.add("hidden");

      if (email === "") {
        emailError.innerText = "Email is required!";
        emailError.classList.remove("hidden");
        isValid = false;
      } else if (!email.match(/^\S+@\S+\.\S+$/)) {
        emailError.innerText = "Invalid email format!";
        emailError.classList.remove("hidden");
        isValid = false;
      }

      if (password === "") {
        passwordError.innerText = "Password is required!";
        passwordError.classList.remove("hidden");
        isValid = false;
      } else if (password.length < 8) {
        passwordError.innerText = "Password must be at least 8 characters!";
        passwordError.classList.remove("hidden");
        isValid = false;
      }

      return isValid;
    }
  </script>
</body>

</html>
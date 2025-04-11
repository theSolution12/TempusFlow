<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
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
      <form action="./backend/forgot_password.php" method="post" class="w-full max-w-sm"
        onsubmit="return validateForm()">
        <h2 class="text-2xl font-bold mb-6 text-center">Forgot Password</h2>

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
          <input class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" id="email"
            name="email" type="email" placeholder="you@example.com" />
          <span id="emailError" class="text-red-500 text-sm hidden"></span>
        </div>

        <div class="mb-6 text-sm text-center text-gray-600">
          Remember your password? <a href="login.php" class="text-blue-600 hover:underline">Login here</a>
        </div>

        <input type="submit" value="Submit"
          class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition" />
      </form>
    </div>

  </div>

  <script>
    function validateForm() {
      let email = document.getElementById("email").value.trim();
      let emailError = document.getElementById("emailError");
      let isValid = true;

      emailError.innerText = "";
      emailError.classList.add("hidden");

      if (email === "") {
        emailError.innerText = "Email is required!";
        emailError.classList.remove("hidden");
        isValid = false;
      } else if (!validateEmail(email)) {
        emailError.innerText = "Invalid email format!";
        emailError.classList.remove("hidden");
        isValid = false;
      }

      return isValid;
    }

    function validateEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(String(email).toLowerCase());
    }
  </script>
</body>

</html>
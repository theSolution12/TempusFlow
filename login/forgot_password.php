<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body class="bg-[url(../assets/login-bg.png)] bg-cover bg-center h-screen flex items-center justify-center">
    <form action="./backend/forgot_password.php" method="post" class="backdrop-blur-lg p-6 rounded-xl shadow-md w-full max-w-sm border-2 border-white" onsubmit="return validateForm()">
        <h2 class="text-2xl text-white text-center font-bold mb-4">Forgot Password</h2>

        <?php
            if (isset($_SESSION['error'])) {
                echo '<p class="text-red-500 text-center font-semibold bg-red-200 p-2 rounded mb-4">' . $_SESSION['error'] . '</p>';
                unset($_SESSION['error']); // Clear the error after displaying
            }
            else if (isset($_SESSION['success'])){
                echo '<p class="text-green-500 text-center font-semibold bg-green-200 p-2 rounded mb-4">' . $_SESSION['success'] . '</p>';
                unset($_SESSION['success']); // Clear the success message after displaying
            }
        ?>

        <div class="mb-4">
            <label class="text-white text-sm font-bold mb-2 ml-4" for="email">Email</label>
            <input class="shadow border-2 rounded-full w-full py-3 px-3 text-white text-sm bg-transparent border-white placeholder:text-white  focus:border-white focus:ring-white focus:outline-none" id="email" name="email" type="email" placeholder="Enter your email">
            <p id="emailError" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        <div class="mb-4">
            <p class="text-white flex gap-2">Remember password? <a href="login.php"
                    class="hover:cursor-pointer">Login here</a></p>
        </div>

        <div class="w-full flex items-center justify-center">
            <input class="bg-white hover:bg-gray-200 font-semibold py-3 px-3 w-full rounded-full cursor-pointer" type="submit" value="Submit">
        </div>
    </form>

    <script>
        function validateForm() {
            let email = document.getElementById("email").value.trim();
            let emailError = document.getElementById("emailError");
            let isValid = true;

            // Reset errors
            emailError.innerText = "";
            emailError.classList.add("hidden");

            // Email validation
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
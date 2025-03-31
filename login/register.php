<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Form with Validation</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>

<body class="bg-[url(../assets/OIP.jpg)] bg-cover bg-center h-screen flex items-center justify-center">
    <form method="post" action="./backend/register.php" class="backdrop-blur-md p-6 rounded shadow-md w-full max-w-sm"
        onsubmit="return validateForm()">
        <h2 class="text-2xl text-white text-center font-bold mb-4">Register Yourself</h2>

        <!-- PHP SESSION ERROR MESSAGE -->
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo '<p class="text-red-500 text-center font-semibold bg-red-200 p-2 rounded mb-4">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']); // Clear the error after displaying
        }
        ?>



        <div class="mb-4">
            <label class="text-white text-sm font-bold mb-2" for="username">Username</label>
            <input class="shadow border rounded w-full py-2 px-3 text-white bg-gray-800" id="username" name="username"
                type="text" placeholder="Enter your username">
            <p id="userNameError" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        <div class="mb-4">
            <label class="text-white text-sm font-bold mb-2" for="email">Email</label>
            <input class="shadow border rounded w-full py-2 px-3 text-white bg-gray-800" id="email" name="email"
                type="email" placeholder="Enter your email">
            <p id="emailError" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        <div class="mb-4">
            <label class="text-white text-sm font-bold mb-2" for="password">Password</label>
            <input class="shadow border rounded w-full py-2 px-3 text-white bg-gray-800" id="password" name="password"
                type="password" placeholder="Enter your password">
            <p id="passwordError" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        <div class="mb-4">
            <label class="text-white text-sm font-bold mb-2" for="confirmPassword">Confirm Password</label>
            <input class="shadow border rounded w-full py-2 px-3 text-white bg-gray-800" name="confirmPassword"
                id="confirmPassword" type="password" placeholder="Enter your password again">
            <p id="confirmPasswordError" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        <div class="mb-4">
            <p class="text-white flex gap-2">Already have an account? <a href="login.php"
                    class="hover:cursor-pointer">Login here</a></p>
        </div>

        <div class="w-full flex items-center justify-center">
            <input class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer"
                type="submit" value="Submit">
        </div>
    </form>

    <script>
        function validateForm() {
            let email = document.getElementById("email").value.trim();
            let password = document.getElementById("password").value.trim();
            let confirmPassword = document.getElementById("confirmPassword").value.trim();
            let emailError = document.getElementById("emailError");
            let passwordError = document.getElementById("passwordError");
            let confirmPasswordError = document.getElementById("confirmPasswordError");
            let isValid = true;

            // Reset error messages
            emailError.innerText = "";
            emailError.classList.add("hidden");
            passwordError.innerText = "";
            passwordError.classList.add("hidden");
            confirmPasswordError.innerText = "";
            confirmPasswordError.classList.add("hidden");

            // Email validation
            if (email === "") {
                emailError.innerText = "Email is required!";
                emailError.classList.remove("hidden");
                isValid = false;
            } else if (!email.match(/^\S+@\S+\.\S+$/)) {
                emailError.innerText = "Invalid email format!";
                emailError.classList.remove("hidden");
                isValid = false;
            }

            // Password validation
            if (password === "") {
                passwordError.innerText = "Password is required!";
                passwordError.classList.remove("hidden");
                isValid = false;
            } else if (password.length < 6) {
                passwordError.innerText = "Password must be at least 6 characters!";
                passwordError.classList.remove("hidden");
                isValid = false;
            }

            // Confirm Password validation
            if (confirmPassword === "") {
                confirmPasswordError.innerText = "Confirm password is required!";
                confirmPasswordError.classList.remove("hidden");
                isValid = false;
            } else if (confirmPassword !== password) {
                confirmPasswordError.innerText = "Passwords do not match!";
                confirmPasswordError.classList.remove("hidden");
                isValid = false;
            }

            return isValid;
        }
    </script>
</body>

</html>
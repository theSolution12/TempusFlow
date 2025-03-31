<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body class="bg-[url(../assets/OIP.jpg)] bg-cover bg-center h-screen flex items-center justify-center">
    <form action="./backend/login.php" method="post" class="backdrop-blur-md p-6 rounded shadow-md w-full max-w-sm" onsubmit="return validateForm()">
        <h2 class="text-2xl text-white text-center font-bold mb-4">Login Form</h2>

        
        <?php if (!empty($_SESSION['error'])): ?>
            <p id="serverError" class="text-red-500 text-center font-bold mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <div class="mb-4">
            <label class="text-white text-sm font-bold mb-2" for="email">Email</label>
            <input class="shadow border rounded w-full py-2 px-3 text-white text-sm bg-gray-800" id="email" name="email" type="email" placeholder="Enter your email">
            <p id="emailError" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        <div class="mb-4">
            <label class="text-white text-sm font-bold mb-2" for="password">Password</label>
            <input class="shadow border rounded w-full py-2 px-3 text-white bg-gray-800" id="password" name="password" type="password" placeholder="Enter your password">
            <p id="passwordError" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>
        
        <div class="mb-4">
            <p class="text-white flex gap-2">Don't have an account? <a href="register.php" class="hover:cursor-pointer">Register here</a></p>
        </div>

        <div class="w-full flex items-center justify-center">
            <input class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer" type="submit" value="Submit">
        </div>
    </form>

    <script>
        function validateForm() {
            let email = document.getElementById("email").value.trim();
            let password = document.getElementById("password").value.trim();
            let emailError = document.getElementById("emailError");
            let passwordError = document.getElementById("passwordError");
            let isValid = true;

            // Reset errors
            emailError.innerText = "";
            emailError.classList.add("hidden");
            passwordError.innerText = "";
            passwordError.classList.add("hidden");

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

            return isValid;
        }
    </script>
</body>
</html>

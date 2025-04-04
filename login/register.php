<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>

<body class="bg-[url(../assets/login-bg.png)] bg-cover bg-center h-screen flex items-center justify-center">
    <form method="post" action="./backend/register.php" class="backdrop-blur-md p-6 rounded-xl shadow-md w-full max-w-sm border-2 border-white"
        onsubmit="return validateForm()">
        <h2 class="text-2xl text-white text-center font-bold mb-4">Register Yourself</h2>


        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo '<p class="text-red-500 text-center font-semibold bg-red-200 p-2 rounded mb-4">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>



        <div class="mb-4">
            <label class="text-white text-sm font-bold mb-2 ml-4" for="username">Username</label>
            <input class="shadow border-2 rounded-full w-full py-3 px-3 text-white text-sm bg-transparent border-white placeholder:text-white  focus:border-white focus:ring-white focus:outline-none" id="username" name="username"
                type="text" placeholder="Enter your username">
            <p id="userNameError" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        <div class="mb-4">
            <label class="text-white text-sm font-bold mb-2 ml-4" for="email">Email</label>
            <input class="shadow border-2 rounded-full w-full py-3 px-3 text-white text-sm bg-transparent border-white placeholder:text-white  focus:border-white focus:ring-white focus:outline-none" id="email" name="email"
                type="email" placeholder="Enter your email">
            <p id="emailError" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        <div class="mb-4">
            <label class="text-white text-sm font-bold mb-2 ml-4" for="password">Password</label>
            <input class="shadow border-2 rounded-full w-full py-3 px-3 text-white text-sm bg-transparent border-white placeholder:text-white  focus:border-white focus:ring-white focus:outline-none" id="password" name="password"
                type="password" placeholder="Enter your password">
            <p id="passwordError" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        <div class="mb-4">
            <label class="text-white text-sm font-bold mb-2 ml-4" for="confirmPassword">Confirm Password</label>
            <input class="shadow border-2 rounded-full w-full py-3 px-3 text-white text-sm bg-transparent border-white placeholder:text-white  focus:border-white focus:ring-white focus:outline-none" name="confirmPassword"
                id="confirmPassword" type="password" placeholder="Enter your password again">
            <p id="confirmPasswordError" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        <div class="mb-4">
            <p class="text-white flex gap-2">Already have an account? <a href="login.php"
                    class="hover:cursor-pointer">Login here</a></p>
        </div>

        <div class="w-full flex items-center justify-center">
            <input class="bg-white hover:bg-gray-200 font-semibold py-3 px-3 w-full rounded-full cursor-pointer"
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
            let userName = document.getElementById("username").value.trim();
            let userNameError = document.getElementById("userNameError");
            let isValid = true;

            emailError.innerText = "";
            emailError.classList.add("hidden");
            passwordError.innerText = "";
            passwordError.classList.add("hidden");
            confirmPasswordError.innerText = "";
            confirmPasswordError.classList.add("hidden");
            userNameError.innerText = "";
            userNameError.classList.add("hidden");

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

            if (confirmPassword === "") {
                confirmPasswordError.innerText = "Confirm password is required!";
                confirmPasswordError.classList.remove("hidden");
                isValid = false;
            } else if (confirmPassword !== password) {
                confirmPasswordError.innerText = "Passwords do not match!";
                confirmPasswordError.classList.remove("hidden");
                isValid = false;
            }

            
            if (userName === "") {
                userNameError.innerText = "Username is required!";
                userNameError.classList.remove("hidden");
                isValid = false;
            } else if (userName.length < 3) {
                userNameError.innerText = "Username must be at least 3 characters!";
                userNameError.classList.remove("hidden");
                isValid = false;
            }

            return isValid;
        }
    </script>
</body>

</html>
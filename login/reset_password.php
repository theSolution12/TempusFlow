<?php
session_start();

if (!isset($_GET['token'])) {
    $_SESSION['error'] = "Invalid reset link.";
    header("location: ./login.php");
    exit();
}

$token = $_GET['token']; // Extract token from URL
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>

    <body class="bg-[url(../assets/login-bg.png)] bg-cover bg-center h-screen flex items-center justify-center">
        <form action="./backend/reset_password.php" method="post"
            class="backdrop-blur-lg p-6 rounded-xl shadow-md w-full max-w-sm border-2 border-white"
            onsubmit="return validateForm()">
            <h2 class="text-2xl text-white text-center font-bold mb-4">Reset Password</h2>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<p class="text-red-500 text-center font-semibold bg-red-200 p-2 rounded mb-4">' . $_SESSION['error'] . '</p>';
                unset($_SESSION['error']); // Clear the error after displaying
            } else if (isset($_SESSION['success'])) {
                echo '<p class="text-green-500 text-center font-semibold bg-green-200 p-2 rounded mb-4">' . $_SESSION['success'] . '</p>';
                unset($_SESSION['success']); // Clear the success message after displaying
            }
            ?>

            <div class="mb-4">
                <label class="text-white text-sm font-bold mb-2 ml-4" for="password">Password</label>
                <input
                    class="shadow border-2 rounded-full w-full py-3 px-3 text-white text-sm bg-transparent border-white placeholder:text-white  focus:border-white focus:ring-white focus:outline-none"
                    id="password" name="password" type="password" placeholder="Enter your password">
                <p id="passwordError" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            <div class="mb-4">
                <label class="text-white text-sm font-bold mb-2 ml-4" for="confirmPassword">Confirm Password</label>
                <input
                    class="shadow border-2 rounded-full w-full py-3 px-3 text-white text-sm bg-transparent border-white placeholder:text-white  focus:border-white focus:ring-white focus:outline-none"
                    name="confirmPassword" id="confirmPassword" type="password" placeholder="Enter your password again">
                <p id="confirmPasswordError" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            <input type="hidden" name="token" id="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="mb-4">
            <p class="text-white flex gap-2">Remember password? <a href="login.php"
                    class="hover:cursor-pointer">Login here</a></p>
        </div>

            <div class="w-full flex items-center justify-center">
                <input class="bg-white hover:bg-gray-200 font-semibold py-3 px-3 w-full rounded-full cursor-pointer"
                    type="submit" value="Submit">
            </div>
        </form>

        <script>
            function validateForm() {
                let password = document.getElementById("password").value.trim();
                let confirmPassword = document.getElementById("confirmPassword").value.trim();
                let passwordError = document.getElementById("passwordError");
                let confirmPasswordError = document.getElementById("confirmPasswordError");
                let isValid = true;

                // Reset errors
                passwordError.innerText = "";
                passwordError.classList.add("hidden");
                confirmPasswordError.innerText = "";
                confirmPasswordError.classList.add("hidden");

                // Password validation
                if (password === "") {
                    passwordError.innerText = "Password is required!";
                    passwordError.classList.remove("hidden");
                    isValid = false;
                } else if (password.length < 8) {
                    passwordError.innerText = "Password must be at least 8 characters long!";
                    passwordError.classList.remove("hidden");
                    isValid = false;
                }

                // Confirm Password validation
                if (confirmPassword === "") {
                    confirmPasswordError.innerText = "Confirm Password is required!";
                    confirmPasswordError.classList.remove("hidden");
                    isValid = false;
                } else if (confirmPassword !== password) {
                    confirmPasswordError.innerText = "Passwords do not match!";
                    confirmPasswordError.classList.remove("hidden");
                    isValid = false;
                }

                return isValid;
            }
    </body>

</html>
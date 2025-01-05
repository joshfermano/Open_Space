<?php
require_once '../../config/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /openspace/src/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="/openspace/src/assets/css/main.css" rel="stylesheet" />
    <link rel="shortcut icon" href="/openspace/src/assets/img/logo_white.jpg" type="image/x-icon" />
    <script src="https://kit.fontawesome.com/61b27e08e6.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <title>Register - OpenSpace</title>
</head>

<body class="bg-gray-100 font-poppins">
    <div class="flex min-h-screen justify-center items-center">
        <div class="bg-white p-8 rounded-xl shadow-lg w-[400px] space-y-6">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <a href="/openspace/src/index.php">
                    <img src="/openspace/src/assets/img/logo_black.jpg" alt="OpenSpace Logo"
                        class="w-[100px] hover:scale-95 transition duration-300" />
                </a>
            </div>

            <h1 class="text-2xl font-bold text-center">Create Account</h1>

            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form id="registerForm" action="/openspace/src/api/auth/register.php" method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium">Username</label>
                    <input type="text" id="username" name="username" required
                        class="w-full p-2 mt-1 border rounded-lg focus:ring-2 focus:ring-black focus:outline-none" />
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium">Email</label>
                    <input type="email" id="email" name="email" required
                        class="w-full p-2 mt-1 border rounded-lg focus:ring-2 focus:ring-black focus:outline-none" />
                </div>

                <div>
                    <label for="first_name" class="block text-sm font-medium">First Name</label>
                    <input type="text" id="first_name" name="first_name" required
                        class="w-full p-2 mt-1 border rounded-lg focus:ring-2 focus:ring-black focus:outline-none" />
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required
                        class="w-full p-2 mt-1 border rounded-lg focus:ring-2 focus:ring-black focus:outline-none" />
                </div>

                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium">Password</label>
                    <div class="flex items-center border rounded-lg overflow-hidden">
                        <input type="password" id="password" name="password" required
                            class="w-full p-2 focus:ring-2 focus:ring-black focus:outline-none" />
                        <button type="button"
                            class="password-toggle flex items-center text-gray-500 hover:text-gray-700 p-2">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="confirm_password" class="block text-sm font-medium">Confirm Password</label>
                    <div class="flex items-center border rounded-lg overflow-hidden">
                        <input type="password" id="confirm_password" name="confirm_password" required
                            class="w-full p-2 focus:ring-2 focus:ring-black focus:outline-none" />
                        <button type="button"
                            class="password-toggle flex items-center text-gray-500 hover:text-gray-700 p-2">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-black text-white p-2 rounded-full hover:bg-white hover:text-black hover:border hover:border-black transition duration-300">
                    Register
                </button>
            </form>

            <div class="text-center border-t pt-4">
                <p class="text-gray-500">
                    Already have an account?
                    <a href="/openspace/src/pages/auth/login.php" class="text-black hover:underline">Log In</a>
                </p>
            </div>
        </div>
    </div>

    <script src="/openspace/src/assets/js/auth.js"></script>
    <script src="/openspace/src/assets/js/passwordToggle.js"></script>
</body>

</html>
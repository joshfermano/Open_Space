<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<nav class="max-w-7xl mx-auto p-4 font-poppins backdrop-blur-lg">
  <div>
    <div class="flex justify-between items-center border-b border-slate-500">
      <div class="w-[100px] h-[100px] cursor-pointer hover:scale-95 transition duration-300">
        <a href="/openspace/src/index.php">
          <img src="/openspace/src/assets/img/logo_white.jpg" alt="OpenSpace Logo" />
        </a>
      </div>

      <div class="flex flex-row space-x-6 text-lg">
        <a class="hover:scale-95 transition duration-300" href="/openspace/src/index.php">Home</a>
        <a class="hover:scale-95 transition duration-300" href="/openspace/src/pages/about.php">About</a>
      </div>

      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Logged In Navigation -->
        <div class="flex space-x-6 items-center">
          <a class="hover:scale-110 transition duration-300" href="/openspace/src/pages/profile/profile.php">
            <i class="fa-solid fa-user"></i> Me
          </a>
          <button class="bg-black px-4 py-2 text-white rounded-full hover:scale-110 border hover:text-black hover:bg-white hover:border-black transition duration-300">
            <a href="/openspace/src/api/auth/logout.php"><i class="fa-regular fa-user"></i> Log Out</a>
          </button>
        </div>
      <?php else: ?>
        <!-- Guest Navigation -->
        <div class="flex space-x-6">
          <button class="bg-black px-4 py-2 text-white rounded-full hover:scale-110 border hover:text-black hover:bg-white hover:border-black transition duration-300">
            <a href="/openspace/src/pages/auth/register.php">
              <i class="fa-solid fa-right-to-bracket"></i> Sign Up
            </a>
          </button>
          <button class="bg-black px-4 py-2 text-white rounded-full hover:scale-110 border hover:text-black hover:bg-white hover:border-black transition duration-300">
            <a href="/openspace/src/pages/auth/login.php">
              <i class="fa-regular fa-user"></i> Log In
            </a>
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>
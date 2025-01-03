<?php
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="/openspace/src/assets/css/main.css" rel="stylesheet" />
  <link rel="shortcut icon" href="/openspace/src/assets/img/logo_white.jpg" type="image/x-icon" />
  <script src="https://kit.fontawesome.com/61b27e08e6.js" crossorigin="anonymous"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <title>OpenSpace - About Us</title>
</head>

<body class="font-poppins">
  <!-- Include Navbar -->
  <?php include_once '../components/navbar.php'; ?>

  <!-- Hero Section -->
  <section class="max-w-6xl mx-auto px-4 py-16 text-center delay-[300ms] duration-[600ms] taos:scale-[0.6] taos:opacity-0" data-taos-offset="400"">
    <h1 class=" text-5xl font-bold mb-6">Transforming Spaces Into Opportunities</h1>
    <p class="text-xl text-gray-600 max-w-3xl mx-auto">OpenSpace connects people with perfect spaces for their needs, whether it's for meetings, events, or stays.</p>
  </section>

  <!-- Features Section -->
  <section class="max-w-6xl mx-auto px-4 py-16 bg-gray-50 delay-[300ms] duration-[600ms] taos:scale-[0.6] taos:opacity-0" data-taos-offset="400"">
    <div class=" grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="text-center p-6">
      <i class="fa-solid fa-building text-4xl mb-4"></i>
      <h3 class="text-xl font-bold mb-2">Quality Spaces</h3>
      <p class="text-gray-600">Carefully curated venues that meet our high standards</p>
    </div>
    <div class="text-center p-6">
      <i class="fa-solid fa-clock text-4xl mb-4"></i>
      <h3 class="text-xl font-bold mb-2">Flexible Booking</h3>
      <p class="text-gray-600">Book by the hour, day, or longer as needed</p>
    </div>
    <div class="text-center p-6">
      <i class="fa-solid fa-shield text-4xl mb-4"></i>
      <h3 class="text-xl font-bold mb-2">Secure Platform</h3>
      <p class="text-gray-600">Safe and secure booking process</p>
    </div>
    </div>
  </section>

  <!-- Mission Section -->
  <section class="max-w-6xl mx-auto px-4 py-16 delay-[300ms] duration-[600ms] taos:scale-[0.6] taos:opacity-0" data-taos-offset="400"">
    <div class=" grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
    <div>
      <img src="/openspace/src/assets/img/logo_black.jpg" alt="About OpenSpace" class="rounded-lg shadow-lg">
    </div>
    <div>
      <h2 class="text-3xl font-bold mb-6">Our Mission</h2>
      <p class="text-gray-600 mb-4">
        At OpenSpace, we believe in making space accessibility simple and efficient. Our platform connects space owners with those who need them, creating opportunities for both parties.
      </p>
      <p class="text-gray-600 mb-4">
        Whether you're looking for a professional meeting room, an event venue, or a comfortable stay, OpenSpace has you covered.
      </p>
    </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="max-w-6xl mx-auto px-4 py-16 bg-gray-50">
    <h2 class="text-3xl font-bold text-center mb-12">Get in Touch</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
      <div class="taos:fade-up" data-taos-offset="200">
        <i class="fa-solid fa-envelope text-2xl mb-4"></i>
        <h3 class="font-bold mb-2">Email</h3>
        <p class="text-gray-600">joshkhovick.fermano@tup.edu.ph</p>
      </div>
      <div>
        <i class="fa-solid fa-phone text-2xl mb-4"></i>
        <h3 class="font-bold mb-2">Phone</h3>
        <p class="text-gray-600">+63 949 945 6005</p>
      </div>
      <div>
        <i class="fa-solid fa-location-dot text-2xl mb-4"></i>
        <h3 class="font-bold mb-2">Location</h3>
        <p class="text-gray-600">TUP Manila, Philippines</p>
      </div>
    </div>
  </section>

  <!-- Include Footer -->
  <?php include_once '../components/footer.php'; ?>
</body>

</html>
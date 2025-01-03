<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link href="/openspace/src/assets/css/main.css" rel="stylesheet" />

  <link
    rel="shortcut icon"
    href="assets/img/logo_white.jpg"
    type="image/x-icon" />

  <script
    src="https://kit.fontawesome.com/61b27e08e6.js"
    crossorigin="anonymous"></script>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Familjen+Grotesk:ital,wght@0,400..700;1,400..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
    rel="stylesheet" />

  <title>OpenSpace</title>
</head>

<body>
  <!-- Navbar -->
  <?php include_once __DIR__ . '/components/navbar.php'; ?>


  <!-- Search and Filter Section -->
  <section class="max-w-6xl mx-auto p-4 font-poppins delay-[300ms] duration-[600ms] taos:scale-[0.6] taos:opacity-0" data-taos-offset="400"">
    <div class=" space-y-4">
    <!-- Search Bar -->
    <div class="relative flex justify-center items-center w-full">
      <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 z-10"></i>
      <input
        type="text"
        id="searchInput"
        class="w-full p-4 pl-12 bg-gray-100 rounded-full focus:outline-none"
        placeholder="Search Rooms" />
    </div>

    <!-- Category Filters -->
    <div class="flex flex-wrap gap-4 p-2" id="categoryFilters">
      <?php
      $query = "SELECT * FROM categories";
      $result = $conn->query($query);
      while ($category = $result->fetch_assoc()) {
      ?>
        <label class="flex items-center space-x-2 cursor-pointer">
          <input
            type="checkbox"
            class="categoryFilter"
            name="category[]"
            value="<?= $category['category_id'] ?>" />
          <span><?= htmlspecialchars($category['name']) ?></span>
        </label>
      <?php } ?>
    </div>
    </div>
  </section>

  <!-- Room Cards Section -->
  <main class="max-w-6xl mx-auto p-4 font-poppins">
    <div id="roomcards" class="p-4 border-t border-black grid grid-cols-1 md:grid-cols-3 gap-6"></div>
  </main>

  <!-- Footer -->
  <?php include_once __DIR__ . '/components/footer.php'; ?>

  <script src="/openspace/src/assets/js/searchRooms.js"></script>
</body>

</html>
<?php
require_once '../../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /openspace/src/pages/auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="/openspace/src/assets/css/main.css" rel="stylesheet" />
    <link rel="shortcut icon" href="/openspace/src/assets/img/logo_white.jpg" type="image/x-icon" />
    <script src="https://kit.fontawesome.com/61b27e08e6.js" crossorigin="anonymous"></script>
    <title>OpenSpace - Add Room</title>
</head>

<body class="font-poppins">
    <?php include_once '../../components/navbar.php'; ?>

    <div class="max-w-4xl mx-auto p-8">
        <h1 class="text-3xl font-bold mb-8">Add New Room</h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/openspace/src/api/rooms/add.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- Room Images -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">Room Images</h2>
                <div id="imagePreviewGrid" class="grid grid-cols-4 gap-4">
                    <label class="border-2 border-dashed border-gray-300 rounded-lg h-32 flex items-center justify-center cursor-pointer hover:border-gray-400">
                        <input type="file" name="room_images[]" id="roomImages" class="hidden" multiple accept="image/*" />
                        <i class="fa-solid fa-plus text-gray-400"></i>
                    </label>
                </div>
                <p class="text-sm text-gray-500">Upload up to 5 images. If no image is uploaded, a default image will be used.</p>
            </div>

            <!-- Room Details -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Room Title</label>
                    <input type="text" name="title" required class="w-full p-2 border rounded-lg" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Category</label>
                    <select name="category_id" required class="w-full p-2 border rounded-lg">
                        <option value="">Select a category</option>
                        <?php
                        $query = "SELECT * FROM categories";
                        $result = $conn->query($query);
                        while ($category = $result->fetch_assoc()) {
                            echo "<option value='" . $category['category_id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="description" required class="w-full p-2 border rounded-lg h-32"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Location</label>
                    <input type="text" name="location" required class="w-full p-2 border rounded-lg" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Price per Hour (₱)</label>
                    <input type="number" name="price" required min="0" step="0.01" value="0" class="w-full p-2 border rounded-lg" />
                    <p class="text-sm text-gray-500 mt-1">Set to 0 for free bookings</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Capacity (persons)</label>
                    <input type="number" name="capacity" required min="1" class="w-full p-2 border rounded-lg" />
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex space-x-4">
                <button type="submit" class="bg-black text-white px-8 py-2 rounded-full hover:bg-gray-800 transition duration-300">
                    Add Room
                </button>
                <a href="/openspace/src/pages/dashboard/rooms.php" class="px-8 py-2 rounded-full border border-black hover:bg-gray-100 transition duration-300">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script src="/openspace/src/assets/js/imagePreview.js"></script>
</body>

</html>
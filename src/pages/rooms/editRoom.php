<?php
require_once '../../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /openspace/src/pages/auth/login.php');
    exit;
}

// Get room ID from URL
$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch room data
$stmt = $conn->prepare("
    SELECT r.*, ri.image_path 
    FROM rooms r 
    LEFT JOIN room_images ri ON r.room_id = ri.room_id AND ri.is_primary = 1
    WHERE r.room_id = ? AND r.owner_id = ?
");
$stmt->bind_param("ii", $room_id, $_SESSION['user_id']);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

// Redirect if room not found or user doesn't own it
if (!$room) {
    header('Location: /openspace/src/pages/dashboard/rooms.php');
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
    <title>OpenSpace - Edit Room</title>
</head>

<body class="font-poppins">
    <!-- Include Navbar -->
    <?php include_once '../../components/navbar.php'; ?>

    <!-- Edit Room Form -->
    <main class="max-w-4xl mx-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Edit Room</h1>
            <button onclick="deleteRoom(<?php echo $room_id; ?>)"
                class="bg-red-600 px-6 py-2 text-white rounded-full hover:bg-red-700 transition duration-300">
                Delete Room
            </button>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/openspace/src/api/rooms/update.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>" />

            <!-- Image Upload Section -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">Room Images</h2>
                <div class="grid grid-cols-4 gap-4">
                    <?php
                    $images_query = "SELECT * FROM room_images WHERE room_id = ?";
                    $stmt = $conn->prepare($images_query);
                    $stmt->bind_param("i", $room_id);
                    $stmt->execute();
                    $images = $stmt->get_result();

                    while ($image = $images->fetch_assoc()):
                    ?>
                        <div class="relative group" data-image-id="<?php echo $image['image_id']; ?>">
                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>"
                                alt="Room Image"
                                class="w-full h-32 object-cover rounded-lg">
                            <div class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center rounded-lg">
                                <button type="button" onclick="deleteImage(<?php echo $image['image_id']; ?>)"
                                    class="text-white"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <label class="border-2 border-dashed border-gray-300 rounded-lg h-32 flex items-center justify-center cursor-pointer hover:border-gray-400">
                        <input type="file" name="room_images[]" class="hidden" multiple accept="image/*" />
                        <i class="fa-solid fa-plus text-gray-400"></i>
                    </label>
                </div>
            </div>

            <!-- Room Details -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">Room Details</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Room Title</label>
                        <input type="text" name="title" maxlength="100" required
                            value="<?php echo htmlspecialchars($room['title']); ?>"
                            class="w-full p-2 border rounded-lg" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Category</label>
                        <select name="category_id" required class="w-full p-2 border rounded-lg">
                            <?php
                            $categories = $conn->query("SELECT * FROM categories");
                            while ($category = $categories->fetch_assoc()):
                                $selected = $category['category_id'] == $room['category_id'] ? 'selected' : '';
                            ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="description" required
                        class="w-full p-2 border rounded-lg h-32"><?php echo htmlspecialchars($room['description']); ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Location</label>
                        <input type="text" name="location" required
                            value="<?php echo htmlspecialchars($room['location']); ?>"
                            class="w-full p-2 border rounded-lg" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Price per Hour (₱)</label>
                        <input type="number" name="price" required min="0" step="0.01"
                            value="<?php echo $room['price']; ?>"
                            class="w-full p-2 border rounded-lg" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Capacity (persons)</label>
                    <input type="number" name="capacity" required min="1"
                        value="<?php echo $room['capacity']; ?>"
                        class="w-full p-2 border rounded-lg" />
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex space-x-4">
                <button type="submit"
                    class="bg-black text-white px-8 py-2 rounded-full hover:bg-gray-800 transition duration-300">
                    Save Changes
                </button>

                <a href="/openspace/src/pages/dashboard/rooms.php"
                    class="px-8 py-2 rounded-full border border-black hover:bg-gray-100 transition duration-300">
                    Cancel
                </a>
            </div>
        </form>
    </main>

    <script src="/openspace/src/assets/js/deleteRoom.js"></script>
</body>

</html>
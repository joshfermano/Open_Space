<?php
require_once '../../config/config.php';

// Check if room ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /openspace/src/index.php');
    exit;
}

$room_id = (int)$_GET['id'];

// Fetch room details with owner info and images
$query = "SELECT r.*, c.name as category_name, u.first_name, u.last_name, ri.image_path 
          FROM rooms r 
          LEFT JOIN categories c ON r.category_id = c.category_id 
          LEFT JOIN users u ON r.owner_id = u.user_id
          LEFT JOIN room_images ri ON r.room_id = ri.room_id AND ri.is_primary = 1
          WHERE r.room_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

if (!$room) {
    header('Location: /openspace/src/index.php');
    exit;
}

// Fetch all room images
$images_query = "SELECT * FROM room_images WHERE room_id = ? ORDER BY is_primary DESC";
$stmt = $conn->prepare($images_query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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


    <title>OpenSpace - <?= htmlspecialchars($room['title']) ?></title>
</head>

<body class="font-poppins">
    <?php include_once '../../components/navbar.php'; ?>

    <main class="max-w-6xl mx-auto p-8">
        <!-- Room Title Section -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold"><?= htmlspecialchars($room['title']) ?></h1>
            <p class="text-gray-500"><?= htmlspecialchars($room['location']) ?></p>
        </div>

        <!-- Image Gallery -->
        <div class="relative mb-8">
            <div class="grid grid-cols-4 gap-2 aspect-[2/1] rounded-xl overflow-hidden">
                <?php if (count($images) > 0): ?>
                    <!-- Primary/First Image (Large) -->
                    <div class="col-span-2 row-span-2 relative group">
                        <img src="<?= $images[0]['image_path'] ?>"
                            alt="<?= htmlspecialchars($room['title']) ?>"
                            class="w-full h-full object-cover hover:opacity-90 transition duration-300 cursor-pointer"
                            onclick="openGallery(0)" />
                    </div>

                    <!-- Additional Images -->
                    <?php for ($i = 1; $i < min(5, count($images)); $i++): ?>
                        <div class="relative group">
                            <img src="<?= $images[$i]['image_path'] ?>"
                                alt="<?= htmlspecialchars($room['title']) ?>"
                                class="w-full h-full object-cover hover:opacity-90 transition duration-300 cursor-pointer"
                                onclick="openGallery(<?= $i ?>)" />
                        </div>
                    <?php endfor; ?>

                    <?php if (count($images) > 5): ?>
                        <div class="relative group cursor-pointer" onclick="openGallery(4)">
                            <img src="<?= $images[4]['image_path'] ?>"
                                alt="<?= htmlspecialchars($room['title']) ?>"
                                class="w-full h-full object-cover brightness-50" />
                            <div class="absolute inset-0 flex items-center justify-center text-white">
                                <span class="text-lg font-semibold">+<?= count($images) - 5 ?> more</span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Fallback Image -->
                    <div class="col-span-4">
                        <img src="/openspace/src/assets/img/logo_black.jpg"
                            alt="<?= htmlspecialchars($room['title']) ?>"
                            class="w-full h-full object-cover rounded-xl" />
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Image Modal -->
        <div id="galleryModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
            <!-- Modal Overlay -->
            <div class="modal-overlay fixed inset-0 bg-black bg-opacity-75"></div>

            <!-- Modal Container -->
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <!-- Close Button -->
                <button onclick="closeGallery()"
                    class="fixed top-6 right-6 text-white bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 z-50">
                    <span class="text-2xl">&times;</span>
                </button>

                <!-- Navigation and Image Container -->
                <div class="relative flex items-center justify-center w-full max-w-7xl mx-auto px-16">
                    <!-- Previous Button -->
                    <button onclick="prevImage()"
                        class="fixed left-6 top-1/2 -translate-y-1/2 text-white bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 z-50">
                        <span class="text-2xl">&lt;</span>
                    </button>

                    <!-- Image Container -->
                    <div class="relative">
                        <img id="modalImage" src="" alt=""
                            class="max-h-[85vh] max-w-full object-contain mx-auto">

                        <!-- Image Counter -->
                        <div class="absolute bottom-4 left-0 right-0 text-center text-white bg-black bg-opacity-50 py-2">
                            <span id="imageCounter">1</span> / <span id="totalImages"><?= count($images) ?></span>
                        </div>
                    </div>

                    <!-- Next Button -->
                    <button onclick="nextImage()"
                        class="fixed right-6 top-1/2 -translate-y-1/2 text-white bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 z-50">
                        <span class="text-2xl">&gt;</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-3 gap-8">
            <!-- Left Column: Room Details -->
            <div class="col-span-2">
                <!-- Host Info -->
                <div class="border-b pb-6 mb-6">
                    <h2 class="text-2xl font-semibold mb-2">
                        <?= htmlspecialchars($room['title']) ?> hosted by <?= htmlspecialchars($room['first_name'] . ' ' . $room['last_name']) ?>
                    </h2>
                    <p class="text-gray-600">
                        Up to <?= htmlspecialchars($room['capacity']) ?> people • <?= htmlspecialchars($room['category_name']) ?>
                    </p>
                </div>

                <!-- Description -->
                <div class="border-b pb-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">About this space</h2>
                    <p class="text-gray-600"><?= nl2br(htmlspecialchars($room['description'])) ?></p>
                </div>
            </div>

            <!-- Right Column: Booking Card -->
            <div class="sticky top-8 h-fit">
                <div class="border rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-2xl font-bold">
                            <?php if ($room['price'] > 0): ?>
                                ₱<?= number_format($room['price'], 2) ?><span class="text-base font-normal">/hour</span>
                            <?php else: ?>
                                <span class="text-green-600">Free</span>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="border-b pb-4 mb-4">
                        <h3 class="text-lg font-semibold mb-2">Unavailable Date Slots</h3>
                        <div id="bookedSlots" class="text-sm space-y-2 max-h-[200px] overflow-y-auto pr-2 custom-scrollbar">
                            <div class="animate-pulse text-gray-500">Loading availability...</div>
                        </div>
                    </div>

                    <!-- Booking Form -->
                    <form id="bookingForm" action="/openspace/src/api/bookings/createBooking.php" method="POST" class="space-y-4">
                        <input type="hidden" name="room_id" value="<?= $room_id ?>">

                        <!-- Date Selection -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Check In Date</label>
                                <input type="date" name="check_in_date" required
                                    min="<?= date('Y-m-d') ?>"
                                    class="w-full p-2 border rounded-lg flatpickr-input" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Check Out Date</label>
                                <input type="date" name="check_out_date" required
                                    min="<?= date('Y-m-d') ?>"
                                    class="w-full p-2 border rounded-lg flatpickr-input" />
                            </div>
                        </div>

                        <!-- Time Selection -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Start Time</label>
                                <input type="time" name="start_time" required
                                    class="w-full p-2 border rounded-lg flatpickr-input" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">End Time</label>
                                <input type="time" name="end_time" required
                                    class="w-full p-2 border rounded-lg flatpickr-input" />
                            </div>
                        </div>

                        <!-- Book Button -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button type="submit"
                                class="w-full bg-black text-white py-3 rounded-lg font-semibold border hover:bg-white hover:text-black hover:border-black transition duration-300">
                                Book Now
                            </button>
                        <?php else: ?>
                            <a href="/openspace/src/pages/auth/login.php"
                                class="block w-full text-center bg-black text-white py-3 rounded-lg font-semibold border hover:bg-white hover:text-black hover:border-black transition duration-300">
                                Login to Book
                            </a>
                        <?php endif; ?>
                    </form>

                    <!-- Price Display Section -->
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-2xl font-bold">
                            <?php if ($room['price'] > 0): ?>
                                ₱<?= number_format($room['price'], 2) ?><span class="text-base font-normal">/hour</span>
                            <?php else: ?>
                                <span class="text-green-600">Free</span>
                            <?php endif; ?>
                        </span>
                    </div>

                    <!-- Price Calculator - Only show if room has a price -->
                    <div id="priceCalculator" class="mt-6 space-y-4 border-t pt-4 hidden">
                        <?php if ($room['price'] > 0): ?>
                            <div class="flex justify-between">
                                <span>Room rate</span>
                                <span>₱<span id="basePrice">0.00</span></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Service fee (10%)</span>
                                <span>₱<span id="serviceFee">0.00</span></span>
                            </div>
                            <div class="flex justify-between font-bold border-t pt-4">
                                <span>Total</span>
                                <span>₱<span id="totalPrice">0.00</span></span>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-green-600 font-semibold">
                                This room is free to use
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const roomImages = <?php echo json_encode(array_column($images, 'image_path')); ?>;
    </script>

    <script src="/openspace/src/assets/js/bookCalendar.js"></script>
    <script src="/openspace/src/assets/js/bookedSlots.js"></script>
    <script src="/openspace/src/assets/js/viewRoom.js"></script>
    <script src="/openspace/src/assets/js/createBooking.js"></script>
    <script src="/openspace/src/assets/js/imageGallery.js"></script>
</body>

</html>
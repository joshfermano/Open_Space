<?php
require_once '../../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /openspace/src/pages/auth/login.php');
    exit;
}

// Fetch pending bookings
$pending_bookings_query = "
    SELECT b.*, r.title, r.price, r.location, ri.image_path,
           u.first_name, u.last_name, 
           b.check_in, b.check_out, b.total_price
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    LEFT JOIN room_images ri ON r.room_id = ri.room_id AND ri.is_primary = 1
    JOIN users u ON b.user_id = u.user_id
    WHERE r.owner_id = ? AND b.status = 'pending'
    ORDER BY b.created_at DESC
";

// Fetch approved bookings
$approved_bookings_query = "
    SELECT b.*, r.title, r.price, r.location, ri.image_path,
           u.first_name, u.last_name, 
           b.check_in, b.check_out, b.total_price
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    LEFT JOIN room_images ri ON r.room_id = ri.room_id AND ri.is_primary = 1
    JOIN users u ON b.user_id = u.user_id
    WHERE r.owner_id = ? AND b.status = 'approved'
    ORDER BY b.check_in ASC
";

// Fetch completed bookings
$completed_bookings_query = "
    SELECT b.*, r.title, r.price, r.location, ri.image_path,
           u.first_name, u.last_name, 
           b.check_in, b.check_out, b.total_price
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    LEFT JOIN room_images ri ON r.room_id = ri.room_id AND ri.is_primary = 1
    JOIN users u ON b.user_id = u.user_id
    WHERE r.owner_id = ? AND b.status = 'completed'
    ORDER BY b.check_in ASC
";

// Fetch rooms
$rooms_query = "
    SELECT r.*, ri.image_path, c.name as category_name,
           (SELECT COUNT(*) FROM bookings b WHERE b.room_id = r.room_id AND b.status = 'approved') as active_bookings
    FROM rooms r
    LEFT JOIN room_images ri ON r.room_id = ri.room_id AND ri.is_primary = 1
    LEFT JOIN categories c ON r.category_id = c.category_id
    WHERE r.owner_id = ?
    ORDER BY r.created_at DESC
";

// Execute queries
$stmt = $conn->prepare($pending_bookings_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pending_bookings = $stmt->get_result();

$stmt = $conn->prepare($approved_bookings_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$approved_bookings = $stmt->get_result();

$stmt = $conn->prepare($completed_bookings_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$completed_bookings = $stmt->get_result();

$stmt = $conn->prepare($rooms_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$rooms = $stmt->get_result();

// Calculate total earnings
$total_earnings = 0;
while ($booking = $approved_bookings->fetch_assoc()) {
    $total_earnings += $booking['total_price'];
}
$approved_bookings->data_seek(0); // Reset result pointer for reuse
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
    <title>OpenSpace - My Rooms</title>
</head>

<body class="font-poppins">
    <?php include_once '../../components/navbar.php'; ?>

    <section class="max-w-6xl mx-auto p-8">
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex justify-between items-center">
                <span><?php echo htmlspecialchars($_GET['success']); ?></span>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <section class="max-w-6xl mx-auto p-8">
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-12">
                <h1 class="text-4xl font-bold">My Rooms</h1>
                <a href="/openspace/src/pages/rooms/addRoom.php">
                    <button class="px-6 py-2 bg-black text-white rounded-full border hover:border-black hover:bg-white hover:text-black hover:scale-95 transition duration-300">
                        Add Room
                    </button>
                </a>
            </div>

            <!-- Total Earnings Section -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">My Earnings</h2>
                <div class="p-4 bg-green-100 text-green-700 rounded-lg">
                    <p class="text-xl font-semibold">₱<?= number_format($total_earnings, 2) ?></p>
                </div>
            </div>

            <!-- Pending Bookings Section -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Pending Room Bookings</h2>
                <?php if ($pending_bookings->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while ($booking = $pending_bookings->fetch_assoc()): ?>
                            <div class="border border-gray-300 p-4 rounded-xl">
                                <div class="shadow-lg p-6 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <!-- Booking Details -->
                                        <div class="flex items-center space-x-6">
                                            <img class="h-[100px] w-[100px] object-cover rounded-lg"
                                                src="<?= $booking['image_path'] ?? '/openspace/src/assets/img/logo_black.jpg' ?>"
                                                alt="Room Image" />
                                            <div>
                                                <h3 class="text-2xl font-bold"><?= htmlspecialchars($booking['title']) ?></h3>
                                                <p class="text-gray-600">Booked by: <?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></p>
                                                <p class="text-gray-600">Total Price: ₱<?= number_format($booking['total_price'], 2) ?></p>
                                                <p class="text-gray-600">
                                                    Date: <?= date('F j, Y', strtotime($booking['check_in'])) ?>
                                                    <?php if (date('Y-m-d', strtotime($booking['check_in'])) !== date('Y-m-d', strtotime($booking['check_out']))): ?>
                                                        - <?= date('F j, Y', strtotime($booking['check_out'])) ?>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="text-gray-600">
                                                    Time: <?= date('g:i A', strtotime($booking['check_in'])) ?> -
                                                    <?= date('g:i A', strtotime($booking['check_out'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <!-- Action Buttons -->
                                        <div class="flex space-x-4">
                                            <button type="button"
                                                onclick="handleBookingAction(<?= $booking['booking_id'] ?>, 'approve')"
                                                class="px-6 py-2 bg-green-600 text-white rounded-full hover:bg-green-700 transition duration-300">
                                                Accept
                                            </button>
                                            <button type="button"
                                                onclick="handleBookingAction(<?= $booking['booking_id'] ?>, 'reject')"
                                                class="px-6 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition duration-300">
                                                Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No pending bookings at the moment.</p>
                <?php endif; ?>
            </div>

            <!-- Approved Bookings Section -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Approved Room Bookings</h2>
                <?php if ($approved_bookings->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while ($booking = $approved_bookings->fetch_assoc()): ?>
                            <div class="border border-gray-300 p-4 rounded-xl">
                                <div class="shadow-lg p-6 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-6">
                                            <img class="h-[100px] w-[100px] object-cover rounded-lg"
                                                src="<?= $booking['image_path'] ?? '/openspace/src/assets/img/logo_black.jpg' ?>"
                                                alt="Room Image" />
                                            <div>
                                                <h3 class="text-2xl font-bold"><?= htmlspecialchars($booking['title']) ?></h3>
                                                <p class="text-gray-600">Booked by: <?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></p>
                                                <p class="text-gray-600">Total Price: ₱<?= number_format($booking['total_price'], 2) ?></p>
                                                <p class="text-gray-600">
                                                    Date: <?= date('F j, Y', strtotime($booking['check_in'])) ?>
                                                    <?php if (date('Y-m-d', strtotime($booking['check_in'])) !== date('Y-m-d', strtotime($booking['check_out']))): ?>
                                                        - <?= date('F j, Y', strtotime($booking['check_out'])) ?>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="text-gray-600">
                                                    Time: <?= date('g:i A', strtotime($booking['check_in'])) ?> -
                                                    <?= date('g:i A', strtotime($booking['check_out'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-green-600 font-semibold">Approved</p>
                                            <button onclick="window.location.href='/openspace/src/pages/bookings/viewInvoice.php?id=<?= $booking['booking_id'] ?>'"
                                                class="px-6 py-2 bg-black text-white rounded-full hover:bg-gray-800 transition duration-300">
                                                View Invoice
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No approved bookings at the moment.</p>
                <?php endif; ?>
            </div>

            <!-- Completed Bookings Section -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Completed Room Bookings</h2>
                <?php if ($completed_bookings->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while ($booking = $completed_bookings->fetch_assoc()): ?>
                            <div class="border border-gray-300 p-4 rounded-xl">
                                <div class="shadow-lg p-6 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-6">
                                            <img class="h-[100px] w-[100px] object-cover rounded-lg"
                                                src="<?= $booking['image_path'] ?? '/openspace/src/assets/img/logo_black.jpg' ?>"
                                                alt="Room Image" />
                                            <div>
                                                <h3 class="text-2xl font-bold"><?= htmlspecialchars($booking['title']) ?></h3>
                                                <p class="text-gray-600">Booked by: <?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></p>
                                                <p class="text-gray-600">Total Price: ₱<?= number_format($booking['total_price'], 2) ?></p>
                                                <p class="text-gray-600">
                                                    Date: <?= date('F j, Y', strtotime($booking['check_in'])) ?>
                                                    <?php if (date('Y-m-d', strtotime($booking['check_in'])) !== date('Y-m-d', strtotime($booking['check_out']))): ?>
                                                        - <?= date('F j, Y', strtotime($booking['check_out'])) ?>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="text-gray-600">
                                                    Time: <?= date('g:i A', strtotime($booking['check_in'])) ?> -
                                                    <?= date('g:i A', strtotime($booking['check_out'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="space-y-4 text-right">
                                            <div>
                                                <p class="text-gray-600 font-semibold">Completed</p>
                                            </div>
                                            <button onclick="window.location.href='/openspace/src/pages/bookings/viewInvoice.php?id=<?= $booking['booking_id'] ?>'"
                                                class="px-6 py-2 bg-black text-white rounded-full hover:bg-gray-800 transition duration-300">
                                                View Invoice
                                            </button>
                                            <button onclick="ownerDeleteBooking(<?= $booking['booking_id'] ?>)"
                                                class="px-6 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition duration-300">
                                                Delete Permanently
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No completed bookings at the moment.</p>
                <?php endif; ?>
            </div>

            <!-- Listed Rooms Section -->
            <div>
                <h2 class="text-2xl font-bold mb-6">My Listed Rooms</h2>
                <?php if ($rooms->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php while ($room = $rooms->fetch_assoc()): ?>
                            <div class="shadow-lg rounded-xl bg-white p-4 flex flex-col space-y-4">
                                <img class="w-full h-[200px] object-cover rounded-lg hover:scale-105 transition duration-300"
                                    src="<?= $room['image_path'] ?? '/openspace/src/assets/img/logo_black.jpg' ?>"
                                    alt="<?= htmlspecialchars($room['title']) ?>" />
                                <h3 class="text-xl font-bold text-center"><?= htmlspecialchars($room['title']) ?></h3>
                                <div class="space-y-2 px-2">
                                    <p><span class="font-bold">Location:</span> <?= htmlspecialchars($room['location']) ?></p>
                                    <p><span class="font-bold">Price:</span> ₱<?= number_format($room['price'], 2) ?>/hr</p>
                                    <p><span class="font-bold">Category:</span> <?= htmlspecialchars($room['category_name']) ?></p>
                                    <p><span class="font-bold">Active Bookings:</span> <?= $room['active_bookings'] ?></p>
                                </div>
                                <a href="/openspace/src/pages/rooms/editRoom.php?id=<?= $room['room_id'] ?>"
                                    class="bg-black text-white w-full p-2 rounded-full border hover:border-black hover:bg-white hover:text-black hover:scale-95 transition duration-300 text-center">
                                    Manage Room
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">You haven't listed any rooms yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <script src="/openspace/src/assets/js/handleBooking.js"></script>
        <script src="/openspace/src/assets/js/ownerDeleteBooking.js"></script>
</body>

</html>
<?php
require_once '../../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: /openspace/src/pages/dashboard/bookings.php');
    exit;
}

$booking_id = (int)$_GET['id'];

// Fetch booking details
$stmt = $conn->prepare("
    SELECT b.*, r.title, r.price 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    WHERE b.booking_id = ? AND b.user_id = ? AND b.status = 'pending'
");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header('Location: /openspace/src/pages/dashboard/bookings.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/openspace/src/assets/css/main.css" rel="stylesheet">
    <link rel="shortcut icon" href="/openspace/src/assets/img/logo_white.jpg" type="image/x-icon">
    <script src="https://kit.fontawesome.com/61b27e08e6.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Edit Booking - OpenSpace</title>
</head>

<body class="font-poppins">
    <?php include_once '../../components/navbar.php'; ?>

    <div class="max-w-2xl mx-auto p-8">
        <h1 class="text-3xl font-bold mb-8">Edit Booking</h1>

        <div class="mb-6">
            <h2 class="text-xl font-semibold"><?= htmlspecialchars($booking['title']) ?></h2>
            <p class="text-gray-600">Current Price: ₱<?= number_format($booking['total_price'], 2) ?></p>
        </div>

        <form action="/openspace/src/api/bookings/updateBooking.php" method="POST" class="space-y-6">
            <input type="hidden" name="booking_id" value="<?= $booking_id ?>">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Check In Date</label>
                    <input type="date" name="check_in_date" required
                        min="<?= date('Y-m-d') ?>"
                        value="<?= date('Y-m-d', strtotime($booking['check_in'])) ?>"
                        class="w-full p-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Check Out Date</label>
                    <input type="date" name="check_out_date" required
                        min="<?= date('Y-m-d', strtotime($booking['check_in'])) ?>"
                        value="<?= date('Y-m-d', strtotime($booking['check_out'])) ?>"
                        class="w-full p-2 border rounded-lg">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Start Time</label>
                    <input type="time" name="start_time" required
                        value="<?= date('H:i', strtotime($booking['check_in'])) ?>"
                        class="w-full p-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">End Time</label>
                    <input type="time" name="end_time" required
                        value="<?= date('H:i', strtotime($booking['check_out'])) ?>"
                        class="w-full p-2 border rounded-lg">
                </div>
            </div>

            <div class="flex space-x-4">
                <button type="submit"
                    class="px-8 py-2 bg-black text-white rounded-full hover:bg-gray-800 transition duration-300">
                    Save Changes
                </button>
                <a href="/openspace/src/pages/dashboard/bookings.php"
                    class="px-8 py-2 rounded-full border border-black hover:bg-gray-100 transition duration-300">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script src="/openspace/src/assets/js/editBooking.js"></script>
</body>

</html>
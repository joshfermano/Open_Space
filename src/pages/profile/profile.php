<?php
require_once '../../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /openspace/src/pages/auth/login.php');
    exit;
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch active booking count (only pending and approved)
$stmt = $conn->prepare("
    SELECT COUNT(*) as booking_count 
    FROM bookings 
    WHERE user_id = ? 
    AND status IN ('pending', 'approved')
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$booking_count = $stmt->get_result()->fetch_assoc()['booking_count'];

// Fetch active room count
$stmt = $conn->prepare("SELECT COUNT(*) as room_count FROM rooms WHERE owner_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$room_count = $stmt->get_result()->fetch_assoc()['room_count'];
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
    <title>Profile - OpenSpace</title>
</head>

<body class="font-poppins">
    <?php include_once '../../components/navbar.php'; ?>

    <section class="max-w-6xl mx-auto p-8">
        <h1 class="text-4xl font-bold mb-8">Account Dashboard</h1>
        <h2 class="text-2xl font-semibold mb-12">
            Welcome, <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Active Bookings Card -->
            <div class="shadow-lg rounded-lg p-6 hover:shadow-xl transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <i class="fa-solid fa-book text-4xl"></i>
                    <span class="text-2xl font-bold"><?php echo $booking_count; ?></span>
                </div>
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold">Active Bookings</h2>
                    <p class="text-gray-600">Monitor your active bookings</p>
                </div>
                <a href="/openspace/src/pages/dashboard/bookings.php"
                    class="inline-block mt-4 text-gray-600 hover:text-black hover:underline transition duration-300">
                    View Bookings <i class="fa-solid fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Rooms Card -->
            <div class="shadow-lg rounded-lg p-6 hover:shadow-xl transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <i class="fa-solid fa-bed text-4xl"></i>
                    <span class="text-2xl font-bold"><?php echo $room_count; ?></span>
                </div>
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold">Listed Rooms</h2>
                    <p class="text-gray-600">Manage your listed rooms and spaces</p>
                </div>
                <a href="/openspace/src/pages/dashboard/rooms.php"
                    class="inline-block mt-4 text-gray-600 hover:text-black hover:underline transition duration-300">
                    View Rooms <i class="fa-solid fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="mt-20">
            <h2 class="text-2xl font-bold mb-6">Profile Information</h2>
            <div class="bg-white shadow-lg rounded-lg p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Username</label>
                        <p class="text-lg"><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Email</label>
                        <p class="text-lg"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">First Name</label>
                        <p class="text-lg"><?php echo htmlspecialchars($user['first_name']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Last Name</label>
                        <p class="text-lg"><?php echo htmlspecialchars($user['last_name']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>
<?php
require_once '../../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: /openspace/src/pages/auth/login.php');
  exit;
}

// Fetch bookings with room details and handle completed status in query
$query = "
    SELECT 
        b.*, 
        r.title, 
        r.price, 
        r.location, 
        ri.image_path,
        CASE 
            WHEN b.status = 'approved' AND b.check_out < NOW() THEN 'completed'
            ELSE b.status 
        END as display_status,
        b.status as original_status
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    LEFT JOIN room_images ri ON r.room_id = ri.room_id AND ri.is_primary = 1
    WHERE b.user_id = ? AND b.hidden_from_user = 0
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Group bookings by status
$bookings = [
  'pending' => [],
  'approved' => [],
  'completed' => [],
  'cancelled' => []
];

while ($booking = $result->fetch_assoc()) {
  // Use display_status for grouping
  $status = $booking['display_status'];
  $bookings[$status][] = $booking;

  // If status needs updating in database
  if ($booking['original_status'] === 'approved' && $status === 'completed') {
    $update_stmt = $conn->prepare("
            UPDATE bookings 
            SET status = 'completed'
            WHERE booking_id = ?
        ");
    $update_stmt->bind_param("i", $booking['booking_id']);
    $update_stmt->execute();
  }
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
  <title>OpenSpace - My Bookings</title>
</head>

<body class="font-poppins">
  <?php include_once '../../components/navbar.php'; ?>

  <section class="max-w-6xl mx-auto p-8">
    <h1 class="text-4xl font-bold mb-12">My Bookings</h1>
    <?php if (isset($_GET['success'])): ?>
      <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg">
        <?= htmlspecialchars($_GET['success']) ?>
      </div>
    <?php endif; ?>

    <!-- Booking Categories -->
    <div class="space-y-12">
      <!-- Pending Bookings -->
      <div>
        <h2 class="text-2xl font-bold mb-6">Pending Bookings</h2>
        <?php if (!empty($bookings['pending'])): ?>
          <div class="space-y-4">
            <?php foreach ($bookings['pending'] as $booking): ?>
              <div class="border border-gray-300 p-4 rounded-xl">
                <div class="shadow-lg p-6 rounded-lg">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                      <img class="h-[100px] w-[100px] object-cover rounded-lg"
                        src="<?= $booking['image_path'] ?? '/openspace/src/assets/img/logo_black.jpg' ?>"
                        alt="Room Image" />
                      <div>
                        <h3 class="text-2xl font-bold"><?= htmlspecialchars($booking['title']) ?></h3>
                        <p class="text-gray-600">Total Price: ₱<?= number_format($booking['total_price'], 2) ?></p>
                        <p class="text-gray-600">
                          Date: <?= date('F j, Y', strtotime($booking['check_in'])) ?>
                          <?php if (strtotime($booking['check_in']) !== strtotime($booking['check_out'])): ?>
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
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="text-yellow-600 font-semibold">Pending</p>
                      </div>
                      <div class="flex space-x-2">
                        <a href="/openspace/src/pages/bookings/updateBooking.php?id=<?= $booking['booking_id'] ?>"
                          class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition duration-300">
                          Edit
                        </a>
                        <button type="button"
                          onclick="confirmCancel(<?= $booking['booking_id'] ?>)"
                          class="px-6 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition duration-300">
                          Cancel Booking
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-gray-500">No pending bookings.</p>
        <?php endif; ?>
      </div>

      <!-- Approved Bookings -->
      <div>
        <h2 class="text-2xl font-bold mb-6">Approved Bookings</h2>
        <?php if (!empty($bookings['approved'])): ?>
          <div class="space-y-4">
            <?php foreach ($bookings['approved'] as $booking):
              $check_in_time = strtotime($booking['check_in']);
              $one_hour_before = $check_in_time - 3600;
              $can_cancel = time() < $one_hour_before;
            ?>
              <div class="border border-gray-300 p-4 rounded-xl"
                data-booking-status="approved"
                data-check-in="<?= $booking['check_in'] ?>"
                data-check-out="<?= $booking['check_out'] ?>">
                <div class="shadow-lg p-6 rounded-lg">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                      <img class="h-[100px] w-[100px] object-cover rounded-lg"
                        src="<?= $booking['image_path'] ?? '/openspace/src/assets/img/logo_black.jpg' ?>"
                        alt="Room Image" />
                      <div>
                        <h3 class="text-2xl font-bold"><?= htmlspecialchars($booking['title']) ?></h3>
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
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="text-green-600 font-semibold">Approved</p>
                        <?php if (!$can_cancel): ?>
                          <p class="text-sm text-red-500">Cannot cancel within 1 hour of check-in</p>
                        <?php endif; ?>
                      </div>
                      <?php if ($can_cancel): ?>
                        <button type="button"
                          onclick="confirmCancel(<?= $booking['booking_id'] ?>)"
                          class="px-6 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition duration-300">
                          Cancel Booking
                        </button>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-gray-500">No approved bookings.</p>
        <?php endif; ?>
      </div>

      <!-- Completed Bookings -->
      <div>
        <h2 class="text-2xl font-bold mb-6">Completed Bookings</h2>
        <?php if (!empty($bookings['completed'])): ?>
          <div class="space-y-4">
            <?php foreach ($bookings['completed'] as $booking): ?>
              <div class="border border-gray-300 p-4 rounded-xl opacity-75">
                <div class="shadow-lg p-6 rounded-lg">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                      <img class="h-[100px] w-[100px] object-cover rounded-lg"
                        src="<?= $booking['image_path'] ?? '/openspace/src/assets/img/logo_black.jpg' ?>"
                        alt="Room Image" />
                      <div>
                        <h3 class="text-2xl font-bold"><?= htmlspecialchars($booking['title']) ?></h3>
                        <p class="text-gray-600">Total Price: ₱<?= number_format($booking['total_price'], 2) ?></p>
                        <p class="text-gray-600">
                          Date: <?= date('F j, Y', strtotime($booking['check_in'])) ?>
                        </p>
                        <p class="text-gray-600">
                          Time: <?= date('g:i A', strtotime($booking['check_in'])) ?> -
                          <?= date('g:i A', strtotime($booking['check_out'])) ?>
                        </p>
                      </div>
                    </div>
                    <div class="space-y-4 text-right">
                      <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="text-gray-600 font-semibold">Completed</p>
                      </div>
                      <button onclick="deleteBooking(<?= $booking['booking_id'] ?>)"
                        class="px-6 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition duration-300">
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-gray-500">No completed bookings.</p>
        <?php endif; ?>
      </div>

      <!-- Cancelled Bookings -->
      <div>
        <h2 class="text-2xl font-bold mb-6">Cancelled Bookings</h2>
        <?php if (!empty($bookings['cancelled'])): ?>
          <div class="space-y-4">
            <?php foreach ($bookings['cancelled'] as $booking): ?>
              <div class="border border-gray-300 p-4 rounded-xl opacity-75">
                <div class="shadow-lg p-6 rounded-lg">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                      <img class="h-[100px] w-[100px] object-cover rounded-lg"
                        src="<?= $booking['image_path'] ?? '/openspace/src/assets/img/logo_black.jpg' ?>"
                        alt="Room Image" />
                      <div>
                        <h3 class="text-2xl font-bold"><?= htmlspecialchars($booking['title']) ?></h3>
                        <p class="text-gray-600">Total Price: ₱<?= number_format($booking['total_price'], 2) ?></p>
                        <p class="text-gray-600">
                          Date: <?= date('F j, Y', strtotime($booking['check_in'])) ?>
                        </p>
                        <p class="text-gray-600">
                          Time: <?= date('g:i A', strtotime($booking['check_in'])) ?> -
                          <?= date('g:i A', strtotime($booking['check_out'])) ?>
                        </p>
                      </div>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500">Status</p>
                      <p class="text-red-600 font-semibold">Cancelled</p>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-gray-500">No cancelled bookings.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <script src="/openspace/src/assets/js/bookRoom.js"></script>
  <script src="/openspace/src/assets/js/updatePastBookings.js"></script>
  <script src="/openspace/src/assets/js/deleteCompletedBookings.js"></script>

</body>

</html>
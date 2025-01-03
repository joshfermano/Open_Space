<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to book a room']);
    exit;
}

try {
    $room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
    $check_in_date = $_POST['check_in_date'] ?? '';
    $check_out_date = $_POST['check_out_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';

    if (!$room_id || !$check_in_date || !$check_out_date || !$start_time || !$end_time) {
        throw new Exception('All fields are required');
    }

    // Format datetime strings
    $check_in_datetime = date('Y-m-d H:i:s', strtotime($check_in_date . ' ' . $start_time));
    $check_out_datetime = date('Y-m-d H:i:s', strtotime($check_out_date . ' ' . $end_time));

    // Validate dates
    if (strtotime($check_in_datetime) >= strtotime($check_out_datetime)) {
        throw new Exception('Check out time must be after check in time');
    }

    if (strtotime($check_in_datetime) < strtotime('now')) {
        throw new Exception('Cannot book for past dates');
    }

    $conn->begin_transaction();

    // Check if room exists and get price
    $stmt = $conn->prepare("SELECT price FROM rooms WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();

    if (!$room) {
        throw new Exception('Room not found');
    }

    // Check for conflicting bookings
    $stmt = $conn->prepare("
        SELECT COUNT(*) as booking_count 
        FROM bookings 
        WHERE room_id = ? 
        AND status IN ('pending', 'approved')
        AND (
            (check_in <= ? AND check_out >= ?)
            OR (check_in <= ? AND check_out >= ?)
            OR (check_in >= ? AND check_out <= ?)
        )
    ");

    $stmt->bind_param(
        "issssss",
        $room_id,
        $check_out_datetime,
        $check_in_datetime,
        $check_in_datetime,
        $check_in_datetime,
        $check_in_datetime,
        $check_out_datetime
    );

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['booking_count'] > 0) {
        throw new Exception('This time slot is already booked');
    }

    // Calculate total price (0 for free rooms)
    $hours = (strtotime($check_out_datetime) - strtotime($check_in_datetime)) / 3600;
    $base_price = $room['price'] * $hours;
    $service_fee = $room['price'] > 0 ? ($base_price * 0.1) : 0;
    $total_price = $base_price + $service_fee;

    // Create booking
    $stmt = $conn->prepare("
        INSERT INTO bookings (room_id, user_id, check_in, check_out, total_price, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->bind_param(
        "iissd",
        $room_id,
        $_SESSION['user_id'],
        $check_in_datetime,
        $check_out_datetime,
        $total_price
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to create booking');
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'redirect' => '/openspace/src/pages/dashboard/bookings.php'
    ]);
} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

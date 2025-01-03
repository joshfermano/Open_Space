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
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit;
}

try {
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $check_in_date = $_POST['check_in_date'] ?? '';
    $check_out_date = $_POST['check_out_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';

    if (!$booking_id || !$check_in_date || !$check_out_date || !$start_time || !$end_time) {
        throw new Exception('All fields are required');
    }

    $check_in_datetime = date('Y-m-d H:i:s', strtotime("$check_in_date $start_time"));
    $check_out_datetime = date('Y-m-d H:i:s', strtotime("$check_out_date $end_time"));

    // Validate dates
    if (strtotime($check_in_datetime) >= strtotime($check_out_datetime)) {
        throw new Exception('Check out time must be after check in time');
    }

    if (strtotime($check_in_datetime) < strtotime('now')) {
        throw new Exception('Cannot book for past dates');
    }

    $conn->begin_transaction();

    // Verify booking ownership and get room details
    $stmt = $conn->prepare("
        SELECT b.*, r.price 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.room_id 
        WHERE b.booking_id = ? AND b.user_id = ? AND b.status = 'pending'
    ");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        throw new Exception('Booking not found or cannot be updated');
    }

    // Check for conflicting bookings
    $stmt = $conn->prepare("
        SELECT COUNT(*) as booking_count 
        FROM bookings 
        WHERE room_id = ? 
        AND booking_id != ?
        AND status IN ('pending', 'approved')
        AND (
            (check_in <= ? AND check_out >= ?)
            OR (check_in <= ? AND check_out >= ?)
            OR (check_in >= ? AND check_out <= ?)
        )
    ");

    $stmt->bind_param(
        "iissssss",
        $booking['room_id'],
        $booking_id,
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

    // Calculate new total price
    $hours = (strtotime($check_out_datetime) - strtotime($check_in_datetime)) / 3600;
    $base_price = $booking['price'] * $hours;
    $service_fee = $base_price * 0.1;
    $total_price = $base_price + $service_fee;

    // Update booking
    $stmt = $conn->prepare("
        UPDATE bookings 
        SET check_in = ?, check_out = ?, total_price = ?
        WHERE booking_id = ? AND user_id = ? AND status = 'pending'
    ");

    $stmt->bind_param(
        "ssdii",
        $check_in_datetime,
        $check_out_datetime,
        $total_price,
        $booking_id,
        $_SESSION['user_id']
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to update booking');
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking updated successfully'
    ]);
} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

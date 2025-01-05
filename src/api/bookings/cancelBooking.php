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

    if (!$booking_id) {
        throw new Exception('Invalid booking ID');
    }

    $conn->begin_transaction();

    // Check if booking exists, belongs to user, and get check_in time
    $stmt = $conn->prepare("
        SELECT status, check_in 
        FROM bookings 
        WHERE booking_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Booking not found or unauthorized');
    }

    $booking = $result->fetch_assoc();

    // Check if booking can be cancelled
    if (!in_array($booking['status'], ['pending', 'approved'])) {
        throw new Exception('This booking cannot be cancelled');
    }

    // Check if within 1 hour of check-in time for approved bookings
    if ($booking['status'] === 'approved') {
        $check_in_time = strtotime($booking['check_in']);
        $one_hour_before = $check_in_time - 3600; // 1 hour in seconds

        if (time() >= $one_hour_before) {
            throw new Exception('Cannot cancel bookings within 1 hour of check-in time');
        }
    }

    // Update booking status
    $stmt = $conn->prepare("
        UPDATE bookings 
        SET status = 'cancelled'
        WHERE booking_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);

    if (!$stmt->execute()) {
        throw new Exception('Failed to cancel booking');
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking cancelled successfully'
    ]);
} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

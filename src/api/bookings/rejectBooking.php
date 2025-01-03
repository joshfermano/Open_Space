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

    // Verify room ownership and booking status
    $stmt = $conn->prepare("
        SELECT b.*, r.owner_id 
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        WHERE b.booking_id = ? AND r.owner_id = ?
    ");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Booking not found or unauthorized');
    }

    $booking = $result->fetch_assoc();

    if ($booking['status'] !== 'pending') {
        throw new Exception('This booking cannot be rejected');
    }

    // Update booking status
    $stmt = $conn->prepare("
        UPDATE bookings 
        SET status = 'rejected'
        WHERE booking_id = ?
    ");
    $stmt->bind_param("i", $booking_id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to reject booking');
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking rejected successfully'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

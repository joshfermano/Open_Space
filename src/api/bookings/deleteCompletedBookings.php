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
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;

    if (!$booking_id) {
        throw new Exception('Invalid booking ID');
    }

    $conn->begin_transaction();

    // Verify booking ownership and status
    $stmt = $conn->prepare("
        SELECT b.* FROM bookings b 
        WHERE b.booking_id = ? 
        AND b.user_id = ?
        AND b.status = 'completed'
    ");

    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();

    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Booking not found or cannot be hidden');
    }

    // Hide the booking from user view instead of deleting
    $stmt = $conn->prepare("UPDATE bookings SET hidden_from_user = 1 WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to hide booking');
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Booking hidden successfully']);
} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

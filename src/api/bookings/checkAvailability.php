<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
    $check_in = $_GET['check_in'] ?? '';
    $check_out = $_GET['check_out'] ?? '';

    if (!$room_id || !$check_in || !$check_out) {
        throw new Exception('Missing required parameters');
    }

    $check_in = date('Y-m-d H:i:s', strtotime($check_in));
    $check_out = date('Y-m-d H:i:s', strtotime($check_out));

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
        $check_out,
        $check_in,
        $check_in,
        $check_in,
        $check_in,
        $check_out
    );

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    echo json_encode([
        'success' => true,
        'available' => $result['booking_count'] === 0
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

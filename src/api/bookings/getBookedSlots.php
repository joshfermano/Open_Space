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
    $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

    if (!$room_id) {
        throw new Exception('Invalid room ID');
    }

    $start_date = date('Y-m-01', strtotime($month));
    $end_date = date('Y-m-t', strtotime($month));

    // Fetch approved bookings
    $stmt = $conn->prepare("
        SELECT 
            DATE(check_in) as date,
            check_in as start_datetime,
            check_out as end_datetime
        FROM bookings 
        WHERE room_id = ? 
        AND status = 'approved'
        AND DATE(check_in) BETWEEN ? AND ?
        ORDER BY check_in ASC
    ");

    $stmt->bind_param("iss", $room_id, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $bookedSlots = [];
    while ($row = $result->fetch_assoc()) {
        if (!isset($bookedSlots[$row['date']])) {
            $bookedSlots[$row['date']] = [];
        }
        $bookedSlots[$row['date']][] = [
            'start_datetime' => $row['start_datetime'],
            'end_datetime' => $row['end_datetime']
        ];
    }

    echo json_encode([
        'success' => true,
        'bookedSlots' => $bookedSlots
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

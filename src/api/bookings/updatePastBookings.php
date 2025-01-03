<?php
require_once '../../config/config.php';

try {
    $check_stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM bookings 
        WHERE status = 'approved' 
        AND check_out < NOW()
    ");

    $check_stmt->execute();
    $result = $check_stmt->get_result()->fetch_assoc();

    if ($result['count'] > 0) {
        $update_stmt = $conn->prepare("
            UPDATE bookings 
            SET status = 'completed'
            WHERE status = 'approved' 
            AND check_out < NOW()
        ");

        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update past bookings");
        }

        echo json_encode(['success' => true, 'updated' => true]);
    } else {
        echo json_encode(['success' => true, 'updated' => false]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

<?php
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /openspace/src/pages/dashboard/rooms.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /openspace/src/pages/auth/login.php');
    exit;
}

try {
    $room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;

    if (!$room_id) {
        throw new Exception("Invalid room ID");
    }

    // Start transaction
    $conn->begin_transaction();

    // Verify room ownership and get images
    $stmt = $conn->prepare("
        SELECT ri.image_path 
        FROM rooms r 
        LEFT JOIN room_images ri ON r.room_id = ri.room_id 
        WHERE r.room_id = ? AND r.owner_id = ?
    ");
    $stmt->bind_param("ii", $room_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Room not found or unauthorized");
    }

    // Delete images from filesystem
    while ($row = $result->fetch_assoc()) {
        if ($row['image_path']) {
            $filepath = $_SERVER['DOCUMENT_ROOT'] . $row['image_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    // Delete room images from database
    $stmt = $conn->prepare("DELETE FROM room_images WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();

    // Delete room bookings
    $stmt = $conn->prepare("DELETE FROM bookings WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();

    // Delete room
    $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $room_id, $_SESSION['user_id']);

    if (!$stmt->execute()) {
        throw new Exception("Failed to delete room");
    }

    // Commit transaction
    $conn->commit();

    // Remove room directory if it exists
    $room_dir = ROOMS_UPLOAD_PATH . "/{$room_id}";
    if (is_dir($room_dir)) {
        array_map('unlink', glob("$room_dir/*.*"));
        rmdir($room_dir);
    }

    header('Location: /openspace/src/pages/dashboard/rooms.php?success=Room deleted successfully');
    exit;
} catch (Exception $e) {
    $conn->rollback();
    header('Location: /openspace/src/pages/dashboard/rooms.php?error=' . urlencode($e->getMessage()));
    exit;
}

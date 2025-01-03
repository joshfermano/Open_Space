<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $image_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$image_id) {
        throw new Exception("Invalid image ID");
    }

    $conn->begin_transaction();

    // Verify image ownership and get image details
    $stmt = $conn->prepare("
        SELECT ri.image_path, ri.room_id, ri.is_primary
        FROM room_images ri 
        JOIN rooms r ON ri.room_id = r.room_id
        WHERE ri.image_id = ? AND r.owner_id = ?
    ");
    $stmt->bind_param("ii", $image_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Image not found or unauthorized");
    }

    $image = $result->fetch_assoc();

    // Check if it's the only image
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM room_images WHERE room_id = ?");
    $stmt->bind_param("i", $image['room_id']);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['total'];

    if ($count <= 1) {
        throw new Exception("Cannot delete the only image");
    }

    if ($image['is_primary']) {
        throw new Exception("Cannot delete primary image");
    }

    // Delete physical file
    if ($image['image_path']) {
        $filepath = $_SERVER['DOCUMENT_ROOT'] . $image['image_path'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM room_images WHERE image_id = ?");
    $stmt->bind_param("i", $image_id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to delete image");
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

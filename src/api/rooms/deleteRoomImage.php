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
        SELECT ri.image_path, ri.room_id, ri.is_primary, ri.image_id
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

    // Check total number of images
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM room_images WHERE room_id = ?");
    $stmt->bind_param("i", $image['room_id']);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['total'];

    if ($count <= 1) {
        throw new Exception("Cannot delete the only image");
    }

    // If deleting primary image, set another image as primary
    if ($image['is_primary']) {
        // Get next available image
        $stmt = $conn->prepare("
            SELECT image_id 
            FROM room_images 
            WHERE room_id = ? AND image_id != ? 
            LIMIT 1
        ");
        $stmt->bind_param("ii", $image['room_id'], $image_id);
        $stmt->execute();
        $new_primary = $stmt->get_result()->fetch_assoc();

        if ($new_primary) {
            // Set new primary image
            $stmt = $conn->prepare("
                UPDATE room_images 
                SET is_primary = 1 
                WHERE image_id = ?
            ");
            $stmt->bind_param("i", $new_primary['image_id']);
            if (!$stmt->execute()) {
                throw new Exception("Failed to set new primary image");
            }
        } else {
            throw new Exception("Cannot delete the only image");
        }
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
    echo json_encode([
        'success' => true,
        'message' => 'Image deleted successfully'
    ]);
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

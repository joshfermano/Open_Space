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
    // Get and validate room ID
    $room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;

    if (!$room_id) {
        throw new Exception("Invalid room ID");
    }

    // Verify room ownership
    $stmt = $conn->prepare("SELECT room_id FROM rooms WHERE room_id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $room_id, $_SESSION['user_id']);
    $stmt->execute();

    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Room not found or unauthorized");
    }

    // Start transaction
    $conn->begin_transaction();

    // Update room details
    $stmt = $conn->prepare("
        UPDATE rooms 
        SET category_id = ?, 
            title = ?, 
            description = ?, 
            price = ?, 
            location = ?, 
            capacity = ?
        WHERE room_id = ? AND owner_id = ?
    ");

    $stmt->bind_param(
        "issdsiis",
        $_POST['category_id'],
        $_POST['title'],
        $_POST['description'],
        $_POST['price'],
        $_POST['location'],
        $_POST['capacity'],
        $room_id,
        $_SESSION['user_id']
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to update room details");
    }

    // Handle new image uploads if any
    if (isset($_FILES['room_images']) && !empty($_FILES['room_images']['name'][0])) {
        $room_path = ROOMS_UPLOAD_PATH . "/{$room_id}";
        if (!file_exists($room_path)) {
            mkdir($room_path, 0777, true);
        }

        $files = $_FILES['room_images'];
        foreach ($files['name'] as $i => $name) {
            if ($files['error'][$i] === 0) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $filename = uniqid() . "." . $ext;
                $filepath = $room_path . "/" . $filename;

                if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                    $image_url = "/openspace/src/uploads/rooms/{$room_id}/{$filename}";

                    // Check if this is the first image (make it primary if no other images exist)
                    $check_images = $conn->prepare("SELECT COUNT(*) as count FROM room_images WHERE room_id = ?");
                    $check_images->bind_param("i", $room_id);
                    $check_images->execute();
                    $count = $check_images->get_result()->fetch_assoc()['count'];
                    $is_primary = ($count === 0) ? 1 : 0;

                    $stmt = $conn->prepare("INSERT INTO room_images (room_id, image_path, is_primary) VALUES (?, ?, ?)");
                    $stmt->bind_param("isi", $room_id, $image_url, $is_primary);

                    if (!$stmt->execute()) {
                        throw new Exception("Error saving image");
                    }
                } else {
                    throw new Exception("Error uploading image");
                }
            }
        }
    }

    // Commit transaction
    $conn->commit();

    header('Location: /openspace/src/pages/dashboard/rooms.php?success=Room updated successfully');
    exit;
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();

    header('Location: /openspace/src/pages/rooms/editRoom.php?id=' . $room_id . '&error=' . urlencode($e->getMessage()));
    exit;
}

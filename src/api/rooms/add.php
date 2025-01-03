<?php
require_once '../../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /openspace/src/pages/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate inputs
        if (
            empty($_POST['title']) ||
            empty($_POST['category_id']) ||
            empty($_POST['description']) ||
            empty($_POST['location']) ||
            !isset($_POST['price']) || // Changed from empty() to isset()
            empty($_POST['capacity'])
        ) {
            throw new Exception("All fields are required");
        }

        // Validate price is non-negative
        if ($_POST['price'] < 0) {
            throw new Exception("Price cannot be negative");
        }

        // Start transaction
        $conn->begin_transaction();

        // Insert room details
        $stmt = $conn->prepare("INSERT INTO rooms (owner_id, category_id, title, description, price, location, capacity) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "iissdsi",
            $_SESSION['user_id'],
            $_POST['category_id'],
            $_POST['title'],
            $_POST['description'],
            $_POST['price'],
            $_POST['location'],
            $_POST['capacity']
        );

        if (!$stmt->execute()) {
            throw new Exception("Error creating room");
        }

        $room_id = $conn->insert_id;

        // Handle image uploads
        if (!isset($_FILES['room_images']) || empty($_FILES['room_images']['name'][0])) {
            throw new Exception("At least one image is required");
        }

        // Create room directory
        $room_path = ROOMS_UPLOAD_PATH . "/{$room_id}";
        if (!file_exists($room_path)) {
            mkdir($room_path, 0777, true);
        }

        $files = $_FILES['room_images'];
        $uploaded_count = 0;
        $max_images = 5;

        foreach ($files['name'] as $i => $name) {
            if ($uploaded_count >= $max_images) break;

            if (
                $files['error'][$i] === 0
            ) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array(strtolower($ext), $allowed_types)) {
                    throw new Exception("Invalid file type. Allowed types: " . implode(', ', $allowed_types));
                }

                $filename = uniqid() . "." . $ext;
                $filepath = $room_path . "/" . $filename;

                if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                    $is_primary = ($i === 0) ? 1 : 0;
                    $image_url = "/openspace/src/uploads/rooms/{$room_id}/{$filename}";

                    $stmt = $conn->prepare("INSERT INTO room_images (room_id, image_path, is_primary) VALUES (?, ?, ?)");
                    $stmt->bind_param(
                        "isi",
                        $room_id,
                        $image_url,
                        $is_primary
                    );

                    if (!$stmt->execute()) {
                        throw new Exception("Error saving image");
                    }

                    $uploaded_count++;
                } else {
                    throw new Exception("Error uploading image");
                }
            }
        }

        if ($uploaded_count === 0) {
            throw new Exception("No valid images were uploaded");
        }
        // Commit transaction
        $conn->commit();

        header('Location: /openspace/src/pages/dashboard/rooms.php?success=1');
        exit;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();

        // Clean up any uploaded files
        if (isset($room_path) && file_exists($room_path)) {
            array_map('unlink', glob("$room_path/*.*"));
            rmdir($room_path);
        }

        header('Location: /openspace/src/pages/rooms/addRoom.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}

// If not POST request, redirect back
header('Location: /openspace/src/pages/rooms/addRoom.php');
exit;

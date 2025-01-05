<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $categories = isset($_GET['categories']) ? explode(',', $_GET['categories']) : [];
    $categories = array_filter($categories);

    $query = "
        SELECT r.*, c.name as category_name, ri.image_path 
        FROM rooms r 
        LEFT JOIN categories c ON r.category_id = c.category_id
        LEFT JOIN room_images ri ON r.room_id = ri.room_id AND ri.is_primary = 1
        WHERE 1=1
    ";

    $params = [];
    $types = "";

    if (!empty($search)) {
        $query .= " AND (r.title LIKE ? OR r.description LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }

    if (!empty($categories)) {
        $placeholders = str_repeat('?,', count($categories) - 1) . '?';
        $query .= " AND r.category_id IN ($placeholders)";
        $params = array_merge($params, $categories);
        $types .= str_repeat('i', count($categories));
    }

    // Add ORDER BY clause for alphabetical sorting
    $query .= " ORDER BY r.title ASC";

    $stmt = $conn->prepare($query);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $rooms = [];
    while ($room = $result->fetch_assoc()) {
        $rooms[] = [
            'room_id' => $room['room_id'],
            'title' => $room['title'],
            'location' => $room['location'],
            'price' => $room['price'],
            'category_name' => $room['category_name'],
            'image_path' => $room['image_path'] ?? '/openspace/src/assets/img/logo_black.jpg'
        ];
    }

    echo json_encode(['success' => true, 'rooms' => $rooms]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

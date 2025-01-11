<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$booking_id) {
        throw new Exception('Invalid booking ID');
    }

    // Get or create invoice
    $stmt = $conn->prepare("
        SELECT i.*, 
            b.check_in, b.check_out, b.status,
            r.title as room_title, r.location, r.price as hourly_rate,
            u_client.first_name as client_fname, u_client.last_name as client_lname,
            u_owner.first_name as owner_fname, u_owner.last_name as owner_lname,
            ri.image_path
        FROM bookings b
        LEFT JOIN invoices i ON b.booking_id = i.booking_id
        JOIN rooms r ON b.room_id = r.room_id
        JOIN users u_client ON b.user_id = u_client.user_id
        JOIN users u_owner ON r.owner_id = u_owner.user_id
        LEFT JOIN room_images ri ON r.room_id = ri.room_id AND ri.is_primary = 1
        WHERE b.booking_id = ? 
        AND (b.user_id = ? OR r.owner_id = ?)
    ");

    $stmt->bind_param("iii", $booking_id, $_SESSION['user_id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invoice not found or unauthorized');
    }

    $data = $result->fetch_assoc();

    // Calculate if no invoice exists
    if (!$data['invoice_id']) {
        $hours = (strtotime($data['check_out']) - strtotime($data['check_in'])) / 3600;
        $base_price = $data['hourly_rate'] * $hours;
        $service_fee = $base_price * 0.10;
        $total_amount = $base_price + $service_fee;

        // Create invoice
        $stmt = $conn->prepare("
            INSERT INTO invoices (booking_id, invoice_number, base_price, service_fee, total_amount)
            VALUES (?, ?, ?, ?, ?)
        ");

        $invoice_number = 'INV-' . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
        $stmt->bind_param("isddd", $booking_id, $invoice_number, $base_price, $service_fee, $total_amount);
        $stmt->execute();

        $data['invoice_id'] = $conn->insert_id;
        $data['invoice_number'] = $invoice_number;
        $data['base_price'] = $base_price;
        $data['service_fee'] = $service_fee;
        $data['total_amount'] = $total_amount;
    }

    $invoice_data = [
        'invoice_id' => $data['invoice_number'],
        'date_issued' => date('F j, Y', strtotime($data['date_issued'] ?? 'now')),
        'room_details' => [
            'title' => $data['room_title'],
            'location' => $data['location'],
            'image' => $data['image_path'] ?? '/openspace/src/assets/img/logo_black.jpg'
        ],
        'booking_details' => [
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'duration' => number_format((strtotime($data['check_out']) - strtotime($data['check_in'])) / 3600, 2) . ' hours',
            'hourly_rate' => floatval($data['hourly_rate'])
        ],
        'client_name' => $data['client_fname'] . ' ' . $data['client_lname'],
        'owner_name' => $data['owner_fname'] . ' ' . $data['owner_lname'],
        'payment_details' => [
            'base_price' => floatval($data['base_price']),
            'service_fee' => floatval($data['service_fee']),
            'total_amount' => floatval($data['total_amount'])
        ],
        'status' => $data['status']
    ];

    echo json_encode(['success' => true, 'invoice' => $invoice_data]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

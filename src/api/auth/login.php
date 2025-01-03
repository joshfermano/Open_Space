<?php
// Fix paths by removing ./src/
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    try {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];

                $response['success'] = true;
                $response['message'] = 'Login successful';
            } else {
                throw new Exception('Invalid password');
            }
        } else {
            throw new Exception('User not found');
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

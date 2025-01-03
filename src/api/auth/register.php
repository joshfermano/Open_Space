<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    try {
        // Get and sanitize inputs
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $first_name = sanitizeInput($_POST['first_name']);
        $last_name = sanitizeInput($_POST['last_name']);

        // Validate inputs
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }

        if (!validatePassword($password)) {
            throw new Exception('Password must be at least 8 characters');
        }

        // Check if email or username exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Email or username already exists');
        }

        // Hash password
        $hashed_password = hashPassword($password);

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $first_name, $last_name);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Registration successful';
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;
        } else {
            throw new Exception('Registration failed: ' . $conn->error);
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

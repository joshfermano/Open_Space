<?php
// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Default XAMPP username
define('DB_PASS', '');             // Default XAMPP password
define('DB_NAME', 'openspace');    // Your database name

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 (supports emojis and special characters)
$conn->set_charset("utf8mb4");

// Optional: Set timezone if needed
date_default_timezone_set('Asia/Manila');

// Return the connection
return $conn;

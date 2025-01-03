<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
$conn = require_once __DIR__ . '/database.php';

// Define site-wide constants
define('SITE_URL', 'http://localhost/openspace');
define('UPLOAD_PATH', __DIR__ . '/../uploads');
define('ROOMS_UPLOAD_PATH', UPLOAD_PATH . '/rooms');
define('ROOMS_UPLOAD_URL', SITE_URL . '/src/uploads/rooms');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0777, true);
if (!file_exists(ROOMS_UPLOAD_PATH)) mkdir(ROOMS_UPLOAD_PATH, 0777, true);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

return $conn;

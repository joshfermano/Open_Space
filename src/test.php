<?php
require_once __DIR__ . '/config/config.php';

$query = "SELECT * FROM categories LIMIT 1";
$result = $conn->query($query);

if ($result) {
    echo "Database connected and query successful";
    $row = $result->fetch_assoc();
    echo "<pre>";
    print_r($row);
    echo "</pre>";
} else {
    echo "Error: " . $conn->error;
}

<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'school_erp');

// Application Configuration
define('APP_NAME', 'Netcoder IT ERP');
define('APP_URL', 'http://localhost/nfms'); // Update this for live server

// Establishing Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set Charset
$conn->set_charset("utf8mb4");

// Global Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

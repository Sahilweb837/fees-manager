<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Function to check access level
function checkAccess($allowed_roles) {
    global $role;
    if (!in_array($role, $allowed_roles)) {
        echo "<script>alert('Access Denied!'); window.history.back();</script>";
        exit();
    }
}
?>

<?php
session_start();
require_once '../includes/db.php';

if (isset($_SESSION['user_id'])) {
    logActivity($conn, $_SESSION['user_id'], "Logout", "User logged out of the system.");
}

session_destroy();
header("Location: ../index.php");
exit();
?>

<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    $user = getLoggedInUser($conn);
    redirectByRole($user['role_name']);
} else {
    header("Location: login.php");
    exit();
}
?>

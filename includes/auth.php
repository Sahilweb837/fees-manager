<?php
require_once dirname(__DIR__) . '/config/database.php';

/**
 * Authentication Helper Functions
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function checkLogin() {
    if (!isLoggedIn()) {
        header("Location: " . APP_URL . "/login.php");
        exit();
    }
}

function getLoggedInUser($conn) {
    if (!isLoggedIn()) return null;
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function hasPermission($permission_slug) {
    // This is a placeholder for dynamic permission checking
    // In a full implementation, you'd check role_permissions table
    return true; 
}

function redirectByRole($role_name) {
    switch ($role_name) {
        case 'Super Admin':
            header("Location: " . APP_URL . "/superadmin/dashboard.php");
            break;
        case 'Root Admin':
            header("Location: " . APP_URL . "/rootadmin/dashboard.php");
            break;
        case 'Admin':
            header("Location: " . APP_URL . "/admin/dashboard.php");
            break;
        default:
            header("Location: " . APP_URL . "/staff/dashboard.php");
            break;
    }
    exit();
}
?>

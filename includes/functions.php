<?php
/**
 * General Utility Functions
 */

// Sanitize Input Data
function sanitize($data) {
    global $conn;
    return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)));
}

// Generate CSRF Token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Format Currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Activity Logging
function logActivity($conn, $user_id, $action) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $action, $ip);
    $stmt->execute();
}

// Alert Messaging
function setAlert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function showAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        echo "<script>
            Swal.fire({
                icon: '{$alert['type']}',
                title: '{$alert['message']}',
                showConfirmButton: false,
                timer: 2000
            });
        </script>";
    }
}
?>

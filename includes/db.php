<?php
// 1. Enhanced Session Configuration for Live Servers
if (session_status() === PHP_SESSION_NONE) {
    // Force session to use cookies and set global path
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    
    // Auto-detect secure cookie status
    $is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443);
    
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie
        'path'     => '/',
        'domain'   => $_SERVER['HTTP_HOST'],
        'secure'   => $is_secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// Enable mysqli exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$user = "mhqhxuaasp"; // Update this with Cloudways Database Username
$pass = "4m3xU8bTVq"; // Update this with Cloudways Database Password
$dbname = "mhqhxuaasp"; // Update this with Cloudways Database Name

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
} catch (mysqli_sql_exception $e) {
    die("Database Connection failed: " . $e->getMessage() . "<br><br><b>Note:</b> If you are on Cloudways, make sure to update the database credentials in <code>includes/db.php</code>.");
}

// Auto-detect absolute APP_URL robustly
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain_host = $_SERVER['HTTP_HOST'];
$doc_root = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
$dir_path = str_replace('\\', '/', dirname(__DIR__)); // Project root directory
$base_folder = str_replace($doc_root, '', $dir_path);
define('APP_URL', $protocol . $domain_host . $base_folder);

// Function to log activity
function logActivity($conn, $user_id, $action, $details = "") {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
}
?>
    
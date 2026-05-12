<?php
// Enable mysqli exceptions so we can catch them cleanly
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

// Function to log activity
function logActivity($conn, $user_id, $action, $details = "") {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
}
?>

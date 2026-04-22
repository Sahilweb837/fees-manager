<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fees_management";

echo "<h2>Database Setup Process</h2>";

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "Database `$dbname` created successfully.<br>";
    
    $pdo->exec("USE `$dbname`");

    // Create Activity Logs
    $pdo->exec("CREATE TABLE IF NOT EXISTS `activity_logs` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `user_id` int(11) NOT NULL,
      `action` varchar(255) NOT NULL,
      `details` text DEFAULT NULL,
      `ip_address` varchar(45) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `activity_logs` created.<br>";

    // Create Students
    $pdo->exec("CREATE TABLE IF NOT EXISTS `students` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `student_name` varchar(100) NOT NULL,
      `contact` varchar(20) NOT NULL,
      `course` varchar(100) NOT NULL,
      `status` enum('active','inactive') NOT NULL DEFAULT 'active',
      `added_by` int(11) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `students` created.<br>";

    // Create Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `username` varchar(50) NOT NULL UNIQUE,
      `password` varchar(255) NOT NULL,
      `role` enum('super_admin','admin','employee') NOT NULL DEFAULT 'employee',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `users` created.<br>";

    // Create Fees
    $pdo->exec("CREATE TABLE IF NOT EXISTS `fees` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `student_id` int(11) NOT NULL,
      `fee_type` enum('monthly','registration','exam') NOT NULL,
      `amount` decimal(10,2) NOT NULL,
      `status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
      `collected_by` int(11) DEFAULT NULL,
      `date_collected` timestamp NOT NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `fees` created.<br>";

    // Add Constraints if not exist (using try/catch to ignore if already exists)
    try {
        $pdo->exec("ALTER TABLE `activity_logs` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;");
        $pdo->exec("ALTER TABLE `students` ADD FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION;");
        $pdo->exec("ALTER TABLE `fees` ADD FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;");
        $pdo->exec("ALTER TABLE `fees` ADD FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;");
    } catch(PDOException $e) {} // Ignore foreign key duplicate errors

    // Clear existing users and insert defaults
    $pdo->exec("TRUNCATE TABLE `users`;");
    $pass_hash = password_hash('password', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO `users` (`username`, `password`, `role`) VALUES (?, ?, ?)");
    $stmt->execute(['superadmin', $pass_hash, 'super_admin']);
    $stmt->execute(['admin', $pass_hash, 'admin']);
    $stmt->execute(['employee', $pass_hash, 'employee']);
    
    echo "Default users inserted.<br>";
    echo "<br><div style='color:green;font-weight:bold;'>Setup Completed Successfully!</div>";
    echo "<br><a href='index.php'>Go to Login (Password is 'password')</a>";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>

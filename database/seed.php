<?php
require_once 'config/database.php';

// Seed Roles
$conn->query("INSERT IGNORE INTO roles (id, role_name) VALUES (1, 'Super Admin'), (2, 'Root Admin'), (3, 'Admin'), (4, 'Staff')");

// Seed Categories
$conn->query("INSERT IGNORE INTO fees_categories (category_name) VALUES ('Tuition Fee'), ('Transport Fee'), ('Hostel Fee'), ('Exam Fee')");
$conn->query("INSERT IGNORE INTO expense_categories (category_name) VALUES ('Salary'), ('Electricity'), ('Internet'), ('Maintenance')");

// Seed a Student
$conn->query("INSERT IGNORE INTO students (admission_no, roll_no, first_name, last_name, class, status) VALUES ('ADM001', '101', 'Rahul', 'Sharma', '10th', 1)");

// Seed a Payment
$conn->query("INSERT IGNORE INTO fees_payments (student_id, fee_category_id, invoice_no, total_amount, paid_amount, payment_date, status) VALUES (1, 1, 'INV-001', 5000, 5000, CURDATE(), 'Paid')");

echo "Database seeded successfully!";
?>

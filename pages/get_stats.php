<?php
require_once '../includes/auth.php';

header('Content-Type: application/json');

// 1. Total Students
$res = $conn->query("SELECT COUNT(*) as total FROM students");
$total_students = $res->fetch_assoc()['total'];

// 2. Total Revenue
$res = $conn->query("SELECT SUM(amount) as total FROM fees WHERE status='paid'");
$total_revenue = $res->fetch_assoc()['total'] ?? 0;

// 3. Today's Attendance
$today = date('Y-m-d');
$res = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE attendance_date = '$today' AND status='present'");
$present_today = $res->fetch_assoc()['total'];

// 4. Active Courses
$res = $conn->query("SELECT COUNT(*) as total FROM courses");
$total_courses = $res->fetch_assoc()['total'];

echo json_encode([
    'students' => $total_students,
    'revenue' => number_format($total_revenue, 2),
    'attendance' => $present_today,
    'courses' => $total_courses
]);
?>

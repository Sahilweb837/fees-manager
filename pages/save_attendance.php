<?php
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $status = $_POST['status'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Check if attendance already exists for this day
    $check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND attendance_date = ?");
    $check->bind_param("is", $student_id, $date);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE attendance SET status = ?, attendance_time = ? WHERE student_id = ? AND attendance_date = ?");
        $stmt->bind_param("ssis", $status, $time, $student_id, $date);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, status, attendance_date, attendance_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $student_id, $status, $date, $time);
    }

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Attendance saved',
            'formatted_time' => date('h:i A', strtotime($time))
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>

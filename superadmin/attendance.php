<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkLogin();
$user = getLoggedInUser($conn);

$date = isset($_GET['date']) ? sanitize($_GET['date']) : date('Y-m-d');

// Fetch Students and their attendance for the selected date
$query = "SELECT s.id, s.first_name, s.last_name, s.roll_no, s.class, a.status, a.remarks 
          FROM students s 
          LEFT JOIN attendance a ON s.id = a.student_id AND a.attendance_date = ?
          WHERE s.status = 1 
          ORDER BY s.roll_no ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $date);
$stmt->execute();
$students = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_attendance'])) {
    $attendance_data = $_POST['attendance']; // Array of student_id => status
    foreach ($attendance_data as $student_id => $status) {
        $remarks = sanitize($_POST['remarks'][$student_id] ?? '');
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, status, attendance_date, remarks) 
                                VALUES (?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks)");
        $stmt->bind_param("isss", $student_id, $status, $date, $remarks);
        $stmt->execute();
    }
    setAlert("Attendance saved successfully!", "success");
    header("Location: attendance.php?date=" . $date);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance | ERP Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        #content { width: 100%; padding: 0; min-height: 100vh; background: #f4f7fe; }
        .attendance-radio input:checked + label.present { background-color: #1cc88a; color: white; border-color: #1cc88a; }
        .attendance-radio input:checked + label.absent { background-color: #e74a3b; color: white; border-color: #e74a3b; }
        .attendance-radio input:checked + label.late { background-color: #f6c23e; color: white; border-color: #f6c23e; }
        .attendance-radio input:checked + label.leave { background-color: #36b9cc; color: white; border-color: #36b9cc; }
        .attendance-radio label { cursor: pointer; transition: 0.2s; border: 1px solid #ddd; padding: 5px 15px; border-radius: 5px; font-size: 0.85rem; }
    </style>
</head>
<body>

    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div id="content">
            <?php include '../includes/header.php'; ?>

            <div class="container-fluid px-4">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">Attendance Management</h1>
                    <div class="d-flex align-items-center">
                        <label class="me-2 fw-bold">Select Date:</label>
                        <input type="date" id="attendanceDate" class="form-control shadow-sm" value="<?php echo $date; ?>" onchange="window.location.href='?date='+this.value">
                    </div>
                </div>

                <?php showAlert(); ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold" style="color: #f39c12;">Daily Attendance - <?php echo date('d M, Y', strtotime($date)); ?></h6>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Roll No</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($s = $students->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $s['roll_no']; ?></td>
                                            <td class="fw-bold"><?php echo $s['first_name'] . ' ' . $s['last_name']; ?></td>
                                            <td>
                                                <div class="attendance-radio d-flex gap-2">
                                                    <input type="radio" class="btn-check" name="attendance[<?php echo $s['id']; ?>]" id="p<?php echo $s['id']; ?>" value="Present" <?php echo $s['status'] == 'Present' ? 'checked' : ''; ?> required>
                                                    <label class="btn btn-outline-success present" for="p<?php echo $s['id']; ?>">P</label>

                                                    <input type="radio" class="btn-check" name="attendance[<?php echo $s['id']; ?>]" id="a<?php echo $s['id']; ?>" value="Absent" <?php echo $s['status'] == 'Absent' ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-danger absent" for="a<?php echo $s['id']; ?>">A</label>

                                                    <input type="radio" class="btn-check" name="attendance[<?php echo $s['id']; ?>]" id="l<?php echo $s['id']; ?>" value="Late" <?php echo $s['status'] == 'Late' ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-warning late" for="l<?php echo $s['id']; ?>">L</label>

                                                    <input type="radio" class="btn-check" name="attendance[<?php echo $s['id']; ?>]" id="lv<?php echo $s['id']; ?>" value="Leave" <?php echo $s['status'] == 'Leave' ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-info leave" for="lv<?php echo $s['id']; ?>">LV</label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="remarks[<?php echo $s['id']; ?>]" class="form-control form-control-sm" value="<?php echo $s['remarks']; ?>" placeholder="Optional remarks">
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" name="save_attendance" class="btn btn-primary px-5 shadow" style="background: #e67e22; border: none;">Save Attendance</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>
</html>

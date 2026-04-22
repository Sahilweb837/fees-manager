<?php
require_once '../includes/auth.php';
include '../includes/header.php';

$message = "";
$prefill_student = isset($_GET['student_id']) ? $_GET['student_id'] : '';

// Handle Fee Collection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'collect') {
    $student_id = $_POST['student_id'];
    $fee_type = $_POST['fee_type'];
    $amount = $_POST['amount'];
    
    $stmt = $conn->prepare("INSERT INTO fees (student_id, fee_type, amount, status, collected_by) VALUES (?, ?, ?, 'paid', ?)");
    $stmt->bind_param("isdi", $student_id, $fee_type, $amount, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $fee_id = $conn->insert_id;
        $student_name = $conn->query("SELECT student_name FROM students WHERE id = $student_id")->fetch_assoc()['student_name'];
        logActivity($conn, $_SESSION['user_id'], "Collect Fee", "Collected $fee_type fee of $amount from $student_name.");
        $message = "<div class='alert alert-success'>Fee collected successfully! <a href='invoice.php?id=$fee_id' class='btn btn-sm btn-primary ms-2' target='_blank'>Print Invoice</a></div>";
    } else {
        $message = "<div class='alert alert-danger'>Error collecting fee.</div>";
    }
}

// Fetch Active Students for Dropdown
$students_query = $conn->query("SELECT id, student_name, course FROM students WHERE status = 'active'");

// Fetch Fee History
$fees = $conn->query("
    SELECT f.*, s.student_name, s.course, u.username as collector_name 
    FROM fees f 
    JOIN students s ON f.student_id = s.id 
    LEFT JOIN users u ON f.collected_by = u.id 
    ORDER BY f.id DESC
");
?>

<div class="row">
    <!-- Fee Collection Form -->
    <div class="col-md-4">
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Collect Fee</h5>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="collect">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">-- Choose Student --</option>
                            <?php while($s = $students_query->fetch_assoc()): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo ($prefill_student == $s['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['student_name']) . " (" . htmlspecialchars($s['course']) . ")"; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fee Type</label>
                        <select name="fee_type" class="form-select" required>
                            <option value="monthly">Monthly Fee</option>
                            <option value="registration">Registration Fee</option>
                            <option value="exam">Exam Fee</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="Enter amount" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-check-circle"></i> Save & Collect</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Fee History Table -->
    <div class="col-md-8">
        <div class="card border-dark">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> Fee History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Inv #</th>
                                <th>Student</th>
                                <th>Fee Type</th>
                                <th>Amount</th>
                                <th>Date/Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $fees->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['student_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['course']); ?></small>
                                </td>
                                <td><span class="badge bg-info text-dark"><?php echo ucfirst($row['fee_type']); ?></span></td>
                                <td class="fw-bold text-success">₹<?php echo number_format($row['amount'], 2); ?></td>
                                <td>
                                    <?php echo date('d M Y, h:i A', strtotime($row['date_collected'])); ?><br>
                                    <small class="text-muted">By: <?php echo htmlspecialchars($row['collector_name']); ?></small>
                                </td>
                                <td>
                                    <a href="invoice.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" target="_blank" title="Print Invoice">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

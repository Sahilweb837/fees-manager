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
    $mode = $_POST['payment_mode'];
    $utr = !empty($_POST['utr_number']) ? $_POST['utr_number'] : NULL;
    
    // Smart Duplicate Check
    if ($fee_type == 'monthly') {
        // Check if paid in the CURRENT CALENDAR MONTH
        $check = $conn->prepare("SELECT id FROM fees WHERE student_id = ? AND fee_type = 'monthly' AND MONTH(date_collected) = MONTH(CURRENT_DATE()) AND YEAR(date_collected) = YEAR(CURRENT_DATE())");
        $check->bind_param("i", $student_id);
    } else {
        // Registration and Exam are one-time
        $check = $conn->prepare("SELECT id FROM fees WHERE student_id = ? AND fee_type = ?");
        $check->bind_param("is", $student_id, $fee_type);
    }
    
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $msg_type = ($fee_type == 'monthly') ? "this month" : "already";
        $message = "<div class='alert alert-warning border-0 shadow-sm animate-up'>
            <i class='fas fa-exclamation-triangle me-2'></i> <strong>Duplicate Entry!</strong> This student has already paid the <strong>$fee_type</strong> fee $msg_type.
        </div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO fees (student_id, fee_type, amount, status, collected_by, payment_mode, utr_number) VALUES (?, ?, ?, 'paid', ?, ?, ?)");
        $stmt->bind_param("isdiss", $student_id, $fee_type, $amount, $_SESSION['user_id'], $mode, $utr);
        
        if ($stmt->execute()) {
            $fee_id = $conn->insert_id;
            $student_name_res = $conn->query("SELECT student_name FROM students WHERE id = $student_id")->fetch_assoc();
            $student_name = $student_name_res['student_name'];
            logActivity($conn, $_SESSION['user_id'], "Collect Fee", "Collected $fee_type fee ($mode) of $amount from $student_name.");
            $message = "<div class='alert alert-success border-0 shadow-sm animate-up'>
                <i class='fas fa-check-circle me-2'></i> Fee collected successfully! 
                <a href='invoice.php?id=$fee_id' class='btn btn-sm btn-primary rounded-pill ms-3 px-3' target='_blank'><i class='fas fa-print me-1'></i> Print Invoice</a>
            </div>";
        } else {
            $message = "<div class='alert alert-danger border-0 shadow-sm animate-up'>Error collecting fee.</div>";
        }
    }
}

// Handle Edit Fee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_fee') {
    $fee_id = $_POST['fee_id'];
    $fee_type = $_POST['fee_type'];
    $amount = $_POST['amount'];
    $mode = $_POST['payment_mode'];
    $utr = !empty($_POST['utr_number']) ? $_POST['utr_number'] : NULL;
    
    $stmt = $conn->prepare("UPDATE fees SET fee_type = ?, amount = ?, payment_mode = ?, utr_number = ? WHERE id = ?");
    $stmt->bind_param("sdssi", $fee_type, $amount, $mode, $utr, $fee_id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], "Edit Fee", "Updated fee record #$fee_id ($fee_type).");
        $message = "<div class='alert alert-success border-0 shadow-sm animate-up'>Fee record updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger border-0 shadow-sm animate-up'>Error updating fee record.</div>";
    }
}

// Handle Delete Fee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_fee') {
    $fee_id = $_POST['fee_id'];
    
    $stmt = $conn->prepare("DELETE FROM fees WHERE id = ?");
    $stmt->bind_param("i", $fee_id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], "Delete Fee", "Deleted fee record #$fee_id.");
        $message = "<div class='alert alert-success border-0 shadow-sm animate-up'>Fee record deleted successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger border-0 shadow-sm animate-up'>Error deleting fee record.</div>";
    }
}

// Fetch Active Students for Dropdown with Smart Payment Status
$students_query = $conn->query("
    SELECT s.id, s.student_name, c.course_name,
    (
        SELECT COUNT(DISTINCT fee_type) 
        FROM fees 
        WHERE student_id = s.id 
        AND (
            (fee_type IN ('registration', 'exam'))
            OR 
            (fee_type = 'monthly' AND MONTH(date_collected) = MONTH(CURRENT_DATE()) AND YEAR(date_collected) = YEAR(CURRENT_DATE()))
        )
    ) as current_paid_count
    FROM students s 
    LEFT JOIN courses c ON s.course_id = c.id 
    WHERE s.status = 'active'
    ORDER BY s.student_name ASC
");

// Fetch Fee History
$fees = $conn->query("
    SELECT f.*, s.student_name, s.father_name, c.course_name, u.username as collector_name 
    FROM fees f 
    JOIN students s ON f.student_id = s.id 
    LEFT JOIN courses c ON s.course_id = c.id
    LEFT JOIN users u ON f.collected_by = u.id 
    ORDER BY f.id DESC
");

// Fetch Overview Data
$paid_today = $conn->query("
    SELECT f.*, s.student_name, u.username as collector 
    FROM fees f 
    JOIN students s ON f.student_id = s.id 
    LEFT JOIN users u ON f.collected_by = u.id
    WHERE DATE(f.date_collected) = CURRENT_DATE()
");

$pending_monthly = $conn->query("
    SELECT s.*, c.course_name 
    FROM students s 
    LEFT JOIN courses c ON s.course_id = c.id 
    WHERE s.status = 'active' 
    AND s.id NOT IN (
        SELECT student_id FROM fees 
        WHERE fee_type = 'monthly' 
        AND MONTH(date_collected) = MONTH(CURRENT_DATE()) 
        AND YEAR(date_collected) = YEAR(CURRENT_DATE())
    )
");
?>

<div class="animate-up">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark">Fees Management</h2>
                <p class="text-muted mb-0">Collect fees, track history, and generate professional invoices.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#overviewModal">
                <i class="fas fa-chart-pie me-2"></i>Payment Overview
            </button>
        </div>
    </div>

    <?php echo $message; ?>

    <div class="row g-4">
        <!-- Fee Collection Form -->
        <div class="col-lg-4">
            <div class="card glass-card border-0 shadow-lg sticky-top" style="top: 20px; z-index: 10;">
                <div class="card-header bg-primary text-white border-0 py-3 rounded-top-4">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-wallet me-2"></i> Collect Fee</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" id="feeForm">
                        <input type="hidden" name="action" value="collect">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Student</label>
                            <select name="student_id" id="student_select" class="form-select select2" required>
                                <option value="">-- Search Student --</option>
                                <?php while($s = $students_query->fetch_assoc()): 
                                    $is_fully_paid = ($s['current_paid_count'] >= 3);
                                ?>
                                    <option value="<?php echo $s['id']; ?>" 
                                        <?php echo ($prefill_student == $s['id']) ? 'selected' : ''; ?> 
                                        <?php echo $is_fully_paid ? 'disabled' : ''; ?>
                                        data-course="<?php echo htmlspecialchars($s['course_name']); ?>">
                                        <?php echo htmlspecialchars($s['student_name']); ?> 
                                        <?php echo $is_fully_paid ? '✅ (Paid)' : ''; ?>
                                        (<?php echo htmlspecialchars($s['course_name'] ?? 'No Course'); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fee Category</label>
                            <select name="fee_type" id="fee_type" class="form-select" required>
                                <option value="monthly">Monthly Fee</option>
                                <option value="registration">Registration Fee</option>
                                <option value="exam">Exam Fee</option>
                                <option value="other">Other / Miscellaneous</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">₹</span>
                                <input type="number" step="0.01" name="amount" id="amount" class="form-control border-start-0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Mode</label>
                                <select name="payment_mode" id="payment_mode" class="form-select" onchange="toggleUTR(this.value)" required>
                                    <option value="cash">Cash</option>
                                    <option value="online">Online Transfer</option>
                                    <option value="upi">UPI / GPay</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-none" id="utr_container">
                                <label class="form-label fw-semibold">UTR / Ref No</label>
                                <input type="text" name="utr_number" id="utr_number" class="form-control" placeholder="TXN123...">
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success rounded-pill py-2 fw-bold shadow-sm">
                                <i class="fas fa-check-circle me-2"></i> Save & Collect
                            </button>
                            <button type="button" class="btn btn-outline-primary rounded-pill py-2 fw-bold" onclick="previewInvoice()">
                                <i class="fas fa-eye me-2"></i> Preview Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Fee History Table -->
        <div class="col-lg-8">
            <div class="card glass-card border-0">
                <div class="card-header bg-dark text-white border-0 py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i> Payment History</h5>
                    <div class="d-flex gap-2">
                        <select id="filterFeeType" class="form-select form-select-sm rounded-pill px-3 bg-white bg-opacity-25 text-white border-0">
                            <option value="">All Types</option>
                            <option value="monthly">Monthly</option>
                            <option value="registration">Registration</option>
                            <option value="exam">Exam</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover datatable w-100" id="feeTable">
                            <thead>
                                <tr>
                                    <th class="ps-4">Inv #</th>
                                    <th>Student Details</th>
                                    <th>Fee Details</th>
                                    <th>Collector</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $fees->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-bold">#<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['course_name'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?php echo ucfirst($row['fee_type']); ?></span>
                                        <div class="fw-bold text-success mt-1">₹<?php echo number_format($row['amount'], 2); ?></div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?php echo htmlspecialchars($row['collector_name']); ?></div>
                                        <div class="small text-muted"><?php echo date('d M Y, h:i A', strtotime($row['date_collected'])); ?></div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="invoice.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm rounded-circle shadow-sm" target="_blank" title="Print Invoice">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <button class="btn btn-outline-warning btn-sm rounded-circle shadow-sm ms-1" 
                                            onclick="editFee(<?php echo htmlspecialchars(json_encode($row)); ?>)" title="Edit Fee">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm ms-1" 
                                            onclick="confirmDeleteFee(<?php echo $row['id']; ?>)" title="Delete Fee">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
</div>

<!-- Edit Fee Modal -->
<div class="modal fade" id="editFeeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 bg-warning text-dark rounded-top-4">
                <h5 class="modal-title fw-bold">Edit Fee Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_fee">
                <input type="hidden" name="fee_id" id="edit_fee_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fee Category</label>
                        <select name="fee_type" id="edit_fee_type" class="form-select" required>
                            <option value="monthly">Monthly Fee</option>
                            <option value="registration">Registration Fee</option>
                            <option value="exam">Exam Fee</option>
                            <option value="other">Other / Miscellaneous</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Mode</label>
                            <select name="payment_mode" id="edit_payment_mode" class="form-select" onchange="toggleEditUTR(this.value)" required>
                                <option value="cash">Cash</option>
                                <option value="online">Online Transfer</option>
                                <option value="upi">UPI / GPay</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="edit_utr_container">
                            <label class="form-label fw-semibold">UTR / Ref No</label>
                            <input type="text" name="utr_number" id="edit_utr_number" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Overview Modal -->
<div class="modal fade" id="overviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 bg-dark text-white rounded-top-4 p-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i> Payment Overview & Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-tabs nav-fill border-0 bg-light" id="overviewTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active border-0 py-3 fw-bold" id="paid-tab" data-bs-toggle="tab" data-bs-target="#paid-content" type="button">
                            <i class="fas fa-check-circle me-2 text-success"></i>Paid Today (<?php echo $paid_today->num_rows; ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 py-3 fw-bold" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-content" type="button">
                            <i class="fas fa-clock me-2 text-warning"></i>Pending Monthly (<?php echo $pending_monthly->num_rows; ?>)
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-4" id="overviewTabsContent">
                    <!-- Paid Today Tab -->
                    <div class="tab-pane fade show active" id="paid-content" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Fee Type</th>
                                        <th>Amount</th>
                                        <th>Mode</th>
                                        <th>Collected By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($p = $paid_today->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($p['student_name']); ?></td>
                                        <td><span class="badge bg-primary-subtle text-primary"><?php echo ucfirst($p['fee_type']); ?></span></td>
                                        <td class="fw-bold text-success">₹<?php echo number_format($p['amount'], 2); ?></td>
                                        <td class="text-uppercase small fw-bold"><?php echo $p['payment_mode']; ?></td>
                                        <td><?php echo htmlspecialchars($p['collector']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php if($paid_today->num_rows == 0): ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No payments collected today.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Pending Monthly Tab -->
                    <div class="tab-pane fade" id="pending-content" role="tabpanel">
                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <i class="fas fa-info-circle me-2"></i> Listing active students who haven't paid their <strong>Monthly Fee</strong> for <?php echo date('F Y'); ?>.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Course</th>
                                        <th>Contact</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($pm = $pending_monthly->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($pm['student_name']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($pm['course_name'] ?? 'N/A'); ?></span></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($pm['contact']); ?></td>
                                        <td class="text-end">
                                            <a href="fees.php?student_id=<?php echo $pm['id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                                Collect Fee
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php if($pending_monthly->num_rows == 0): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-success fw-bold">All active students have paid this month's fee!</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const table = $('#feeTable').DataTable();
    
    // Custom Filter for Fee Type
    $('#filterFeeType').on('change', function() {
        table.column(2).search(this.value).draw();
    });
});

function toggleUTR(value) {
    const container = document.getElementById('utr_container');
    if(value === 'cash') {
        container.classList.add('d-none');
    } else {
        container.classList.remove('d-none');
    }
}

function previewInvoice() {
    const studentSelect = document.getElementById('student_select');
    const studentName = studentSelect.options[studentSelect.selectedIndex].text;
    const amount = document.getElementById('amount').value;
    const type = document.getElementById('fee_type').value;
    const mode = document.getElementById('payment_mode').value;
    const utr = document.getElementById('utr_number').value;
    const course = studentSelect.options[studentSelect.selectedIndex].getAttribute('data-course');

    if(!studentSelect.value || !amount) {
        alert("Please select a student and enter an amount first.");
        return;
    }

    const previewHtml = `
        <div class="text-start border p-4 rounded-4 shadow-sm" style="font-family: 'Outfit', sans-serif;">
            <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
                <h4 class="fw-bold">NET<span class="text-primary">CODER</span></h4>
                <div class="text-end">
                    <h5 class="text-uppercase text-muted">PREVIEW</h5>
                    <div class="small">Date: ${new Date().toLocaleDateString()}</div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-6">
                    <div class="text-muted small text-uppercase fw-bold">Student Name</div>
                    <div class="h5 fw-bold mb-0">${studentName}</div>
                    <div class="small text-muted">${course || 'No Course Assigned'}</div>
                </div>
                <div class="col-6 text-end">
                    <div class="text-muted small text-uppercase fw-bold">Payment Info</div>
                    <div class="fw-bold">Mode: <span class="text-primary">${mode.toUpperCase()}</span></div>
                    ${utr ? `<div class="small text-muted">Ref: ${utr}</div>` : ''}
                </div>
            </div>
            <table class="table">
                <thead><tr><th>Description</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                    <tr>
                        <td>${type.charAt(0).toUpperCase() + type.slice(1)} Fee Payment</td>
                        <td class="text-end fw-bold">₹${parseFloat(amount).toLocaleString()}</td>
                    </tr>
                </tbody>
            </table>
            <div class="text-end mt-4">
                <div class="h3 fw-bold text-primary">₹${parseFloat(amount).toLocaleString()}</div>
                <div class="small text-muted">Total Payable</div>
            </div>
        </div>
    `;
    
    document.getElementById('previewContent').innerHTML = previewHtml;
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

function viewDetails(id) {
    // Optional: Fetch and show full transaction details in a modal
    alert("Transaction Details for #" + id + "\nFeature coming soon!");
}

function editFee(data) {
    document.getElementById('edit_fee_id').value = data.id;
    document.getElementById('edit_fee_type').value = data.fee_type;
    document.getElementById('edit_amount').value = data.amount;
    document.getElementById('edit_payment_mode').value = data.payment_mode;
    document.getElementById('edit_utr_number').value = data.utr_number || '';
    
    toggleEditUTR(data.payment_mode);
    new bootstrap.Modal(document.getElementById('editFeeModal')).show();
}

function toggleEditUTR(value) {
    const container = document.getElementById('edit_utr_container');
    if(value === 'cash') {
        container.classList.add('d-none');
    } else {
        container.classList.remove('d-none');
    }
}

function confirmDeleteFee(id) {
    if (confirm("Are you sure you want to delete this fee record?")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_fee">
            <input type="hidden" name="fee_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>

<?php include '../includes/footer.php'; ?>

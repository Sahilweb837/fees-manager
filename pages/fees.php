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

// Fetch Active Students for Dropdown
$students_query = $conn->query("
    SELECT s.id, s.student_name, c.course_name 
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
?>

<div class="animate-up">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold text-dark">Fees Management</h2>
            <p class="text-muted">Collect fees, track history, and generate professional invoices.</p>
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
                                <?php while($s = $students_query->fetch_assoc()): ?>
                                    <option value="<?php echo $s['id']; ?>" <?php echo ($prefill_student == $s['id']) ? 'selected' : ''; ?> data-course="<?php echo htmlspecialchars($s['course_name']); ?>">
                                        <?php echo htmlspecialchars($s['student_name']); ?> (<?php echo htmlspecialchars($s['course_name'] ?? 'No Course'); ?>)
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
                                        <button class="btn btn-outline-info btn-sm rounded-circle shadow-sm ms-1" onclick="viewDetails(<?php echo $row['id']; ?>)" title="View Details">
                                            <i class="fas fa-info-circle"></i>
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

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold">Invoice Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-5 text-center" id="previewContent">
                <!-- Preview injected here -->
                <div class="p-5 border border-dashed rounded-4">
                    <h2 class="text-muted opacity-50">Select student and amount to see preview</h2>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close Preview</button>
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
</script>

<?php include '../includes/footer.php'; ?>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../includes/auth.php';

if (!isset($_GET['id'])) {
    die("Invoice ID is missing.");
}

$fee_id = $_GET['id'];

// Fetch fee and student details
$query = "
    SELECT f.*, s.student_name, s.contact, s.course, u.username as collector_name 
    FROM fees f 
    JOIN students s ON f.student_id = s.id 
    LEFT JOIN users u ON f.collected_by = u.id 
    WHERE f.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $fee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Invoice not found.");
}

$invoice = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $fee_id; ?> - Fees Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f4f7f6; }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(40, 167, 69, 0.1);
            font-weight: bold;
            z-index: 0;
            pointer-events: none;
        }
        .invoice-content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="pt-5">

<div class="container invoice-box position-relative">
    <?php if($invoice['status'] == 'paid'): ?>
        <div class="watermark">PAID</div>
    <?php endif; ?>
    
    <div class="invoice-content">
        <div class="row border-bottom pb-4 mb-4">
            <div class="col-md-6">
                <h2 style="color: var(--primary-color);"><strong>Institute Name</strong></h2>
                <p class="mb-0 text-muted">123 Education Street, City</p>
                <p class="mb-0 text-muted">Phone: +91-9876543210</p>
            </div>
            <div class="col-md-6 text-end">
                <h1 class="text-uppercase text-secondary">INVOICE</h1>
                <p class="mb-0"><strong>Invoice #:</strong> <?php echo str_pad($invoice['id'], 5, '0', STR_PAD_LEFT); ?></p>
                <p class="mb-0"><strong>Date:</strong> <?php echo date('d M Y, h:i A', strtotime($invoice['date_collected'])); ?></p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-12">
                <h5 class="border-bottom pb-2">Student Information</h5>
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="20%">Student Name:</th>
                        <td><?php echo htmlspecialchars($invoice['student_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Contact:</th>
                        <td><?php echo htmlspecialchars($invoice['contact']); ?></td>
                    </tr>
                    <tr>
                        <th>Course:</th>
                        <td><?php echo htmlspecialchars($invoice['course']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-12">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Description / Fee Type</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo ucfirst($invoice['fee_type']); ?> Fee</td>
                            <td class="text-end"><?php echo number_format($invoice['amount'], 2); ?></td>
                        </tr>
                        <tr>
                            <td class="text-end fw-bold">Grand Total:</td>
                            <td class="text-end fw-bold text-success fs-5">₹<?php echo number_format($invoice['amount'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <p><strong>Payment Status:</strong> <span class="badge bg-success fs-6">PAID</span></p>
            </div>
            <div class="col-md-6 text-end">
                <br><br>
                <p class="border-top pt-2 d-inline-block px-4">Authorized Signatory<br>
                <small class="text-muted">(<?php echo htmlspecialchars($invoice['collector_name']); ?>)</small></p>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-4 no-print">
    <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="fas fa-print"></i> Print Invoice</button>
    <button onclick="window.close()" class="btn btn-secondary btn-lg ms-2">Close</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>

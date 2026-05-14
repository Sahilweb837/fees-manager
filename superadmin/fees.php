<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkLogin();
$user = getLoggedInUser($conn);

// Fetch Fees Categories
$categories = $conn->query("SELECT * FROM fees_categories");

// Fetch Recent Payments
$payments = $conn->query("SELECT p.*, s.first_name, s.last_name, c.category_name 
                          FROM fees_payments p 
                          JOIN students s ON p.student_id = s.id 
                          JOIN fees_categories c ON p.fee_category_id = c.id 
                          ORDER BY p.payment_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Management | ERP Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        #content { width: 100%; padding: 0; min-height: 100vh; background: #f4f7fe; }
        .fee-card { border-left: 5px solid #4e73df; }
    </style>
</head>
<body>

    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div id="content">
            <?php include '../includes/header.php'; ?>

            <div class="container-fluid px-4">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">Fee Management</h1>
                    <div class="btn-group shadow-sm">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-tags me-1"></i> Add Category
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#collectFeeModal">
                            <i class="fas fa-hand-holding-usd me-1"></i> Collect Fee
                        </button>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Fee Summaries -->
                    <div class="col-md-4">
                        <div class="card fee-card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <h6 class="text-primary fw-bold text-uppercase small">Total Collected</h6>
                                <h2 class="fw-bold">₹12,45,000</h2>
                                <p class="text-muted small mb-0"><i class="fa fa-arrow-up text-success"></i> 12% increase from last month</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card fee-card h-100 shadow-sm border-0" style="border-left-color: #f6c23e;">
                            <div class="card-body">
                                <h6 class="text-warning fw-bold text-uppercase small">Pending Dues</h6>
                                <h2 class="fw-bold">₹3,20,000</h2>
                                <p class="text-muted small mb-0">Total 45 students with dues</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card fee-card h-100 shadow-sm border-0" style="border-left-color: #e74a3b;">
                            <div class="card-body">
                                <h6 class="text-danger fw-bold text-uppercase small">Overdue Invoices</h6>
                                <h2 class="fw-bold">₹85,000</h2>
                                <p class="text-muted small mb-0">12 invoices pending since 30+ days</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payments Table -->
                    <div class="col-12 mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Fee Payments</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="paymentsTable" class="table table-hover align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Invoice No</th>
                                                <th>Student</th>
                                                <th>Category</th>
                                                <th>Amount</th>
                                                <th>Paid</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($p = $payments->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $p['invoice_no']; ?></td>
                                                <td><?php echo $p['first_name'] . ' ' . $p['last_name']; ?></td>
                                                <td><?php echo $p['category_name']; ?></td>
                                                <td><?php echo formatCurrency($p['total_amount']); ?></td>
                                                <td><?php echo formatCurrency($p['paid_amount']); ?></td>
                                                <td>
                                                    <span class="badge <?php 
                                                        echo $p['status'] == 'Paid' ? 'bg-success' : ($p['status'] == 'Partial' ? 'bg-warning' : 'bg-danger'); 
                                                    ?>">
                                                        <?php echo $p['status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d M, Y', strtotime($p['payment_date'])); ?></td>
                                                <td>
                                                    <a href="../invoice.php?id=<?php echo $p['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa fa-print"></i></a>
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
        </div>
    </div>

    <!-- Collect Fee Modal -->
    <div class="modal fade" id="collectFeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold">Collect Student Fee</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="../ajax/collect_fee.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Search Student</label>
                                <input type="text" class="form-control" placeholder="Enter Student Name or Roll No">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fee Category</label>
                                <select name="category_id" class="form-select">
                                    <?php while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['category_name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Duration</label>
                                <select name="duration" class="form-select">
                                    <option value="1 Year">1 Year</option>
                                    <option value="6 Months">6 Months</option>
                                    <option value="3 Months">3 Months</option>
                                    <option value="45 Days">45 Days</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Amount</label>
                                <input type="number" name="total_amount" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fine (If any)</label>
                                <input type="number" name="fine" class="form-control" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Paid Amount</label>
                                <input type="number" name="paid_amount" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Mode</label>
                                <select name="payment_method" class="form-select">
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="UPI">UPI</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#paymentsTable').DataTable();
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>
</html>

<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkLogin();
$user = getLoggedInUser($conn);

// Fetch Expense Categories
$categories = $conn->query("SELECT * FROM expense_categories");

// Fetch Expenses
$expenses = $conn->query("SELECT e.*, c.category_name, u.name as creator 
                          FROM expenses e 
                          JOIN expense_categories c ON e.category_id = c.id 
                          JOIN users u ON e.created_by = u.id 
                          ORDER BY e.expense_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Management | ERP Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        #content { width: 100%; padding: 0; min-height: 100vh; background: #f4f7fe; }
    </style>
</head>
<body>

    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div id="content">
            <?php include '../includes/header.php'; ?>

            <div class="container-fluid px-4">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">Expense Management</h1>
                    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                        <i class="fas fa-plus fa-sm me-2"></i> Add Expense
                    </button>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="expensesTable" class="table table-hover align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Category</th>
                                                <th>Description</th>
                                                <th>Amount</th>
                                                <th>Created By</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($e = $expenses->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo date('d M, Y', strtotime($e['expense_date'])); ?></td>
                                                <td><span class="badge bg-info text-white"><?php echo $e['category_name']; ?></span></td>
                                                <td><?php echo $e['description']; ?></td>
                                                <td class="fw-bold text-danger"><?php echo formatCurrency($e['amount']); ?></td>
                                                <td><?php echo $e['creator']; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
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

    <!-- Add Expense Modal -->
    <div class="modal fade" id="addExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Add New Expense</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="../ajax/add_expense.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <?php while($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['category_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Save Expense</button>
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
            $('#expensesTable').DataTable();
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>
</html>

<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkLogin();
$user = getLoggedInUser($conn);

// Fetch Staff
$staff_list = $conn->query("SELECT s.*, u.name, u.email, u.status 
                            FROM staff s 
                            JOIN users u ON s.user_id = u.id 
                            ORDER BY s.joining_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management | ERP Pro</title>
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
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">Staff Management</h1>
                    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                        <i class="fas fa-plus fa-sm me-2"></i> Add New Staff
                    </button>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="staffTable" class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Staff ID</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Salary</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($s = $staff_list->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $s['staff_id_card']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../assets/img/default.png" class="rounded-circle me-2" width="30" height="30">
                                                <div>
                                                    <div class="fw-bold"><?php echo $s['name']; ?></div>
                                                    <div class="small text-muted"><?php echo $s['email']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $s['department']; ?></td>
                                        <td><?php echo $s['designation']; ?></td>
                                        <td><?php echo formatCurrency($s['salary']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $s['status'] == 1 ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $s['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></button>
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

    <!-- Add Staff Modal (Simplified) -->
    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold">Add New Staff Member</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="../ajax/add_staff.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Staff ID Card</label>
                                <input type="text" name="staff_id" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Salary</label>
                                <input type="number" name="salary" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Joining Date</label>
                                <input type="date" name="joining_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Staff</button>
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
            $('#staffTable').DataTable();
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>
</html>

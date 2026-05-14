<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkLogin();
$user = getLoggedInUser($conn);

// Fetch Students
$students = $conn->query("SELECT * FROM students ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | ERP Pro</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
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
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">Student Management</h1>
                    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="fas fa-plus fa-sm text-white-50 me-2"></i> Add New Student
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="studentsTable" class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Roll No</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['roll_no']; ?></td>
                                        <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                        <td><?php echo $row['class']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['phone']; ?></td>
                                        <td>
                                            <span class="badge <?php echo $row['status'] == 1 ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $row['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown no-arrow">
                                                <a class="dropdown-toggle text-muted" href="#" role="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                    <li><a class="dropdown-item" href="#"><i class="fa fa-eye me-2 text-muted"></i> View</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fa fa-edit me-2 text-muted"></i> Edit</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#"><i class="fa fa-trash me-2"></i> Delete</a></li>
                                                </ul>
                                            </div>
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

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold">Add New Student</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss.modal="modal"></button>
                </div>
                <form action="../ajax/add_student.php" method="POST" id="addStudentForm">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Guardian Name</label>
                                <input type="text" name="guardian_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Blood Group</label>
                                <select name="blood_group" class="form-select">
                                    <option value="">Select</option>
                                    <option>A+</option><option>A-</option>
                                    <option>B+</option><option>B-</option>
                                    <option>O+</option><option>O-</option>
                                    <option>AB+</option><option>AB-</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Admission No</label>
                                <input type="text" name="admission_no" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Roll No</label>
                                <input type="text" name="roll_no" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Class</label>
                                <select name="class" class="form-select" required>
                                    <option value="">Select Class</option>
                                    <option value="1">Class 1</option>
                                    <option value="2">Class 2</option>
                                    <option value="10th">Class 10th</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option>Male</option>
                                    <option>Female</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Full Address</label>
                                <textarea name="address" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            $('#studentsTable').DataTable({
                "language": {
                    "searchPlaceholder": "Search records...",
                    "search": ""
                }
            });

            // Handle Form Submission via AJAX (Simplified for now)
            $('#addStudentForm').on('submit', function(e) {
                e.preventDefault();
                // AJAX logic here...
                Swal.fire('Success!', 'Student added successfully!', 'success');
                $('#addStudentModal').modal('hide');
            });

            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>
</html>

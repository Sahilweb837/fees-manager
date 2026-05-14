<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkLogin();
if ($_SESSION['role_name'] !== 'Super Admin') {
    die("Access Denied: You do not have permission to view this page.");
}

$user = getLoggedInUser($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard | ERP Pro</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        #content { width: 100%; padding: 0; min-height: 100vh; transition: all 0.3s; background: #f4f7fe; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div id="content">
            <?php include '../includes/header.php'; ?>

            <div class="container-fluid px-4">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">Institute Overview</h1>
                    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" style="background: #e67e22; border: none;">
                        <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
                    </a>
                </div>

                <!-- Stats Row -->
                <div class="row">
                    <!-- Total Interns -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card bg-primary text-white h-100 shadow">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase mb-1">Total Interns</div>
                                    <div class="h3 mb-0 fw-bold">1,250</div>
                                </div>
                                <div class="icon"><i class="fas fa-user-graduate"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Rate -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card bg-dark text-white h-100 shadow">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase mb-1">Today's Presence</div>
                                    <div class="h3 mb-0 fw-bold">92%</div>
                                </div>
                                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Revenue -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card bg-success text-white h-100 shadow">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase mb-1">Monthly Fees</div>
                                    <div class="h3 mb-0 fw-bold">₹4,50,000</div>
                                </div>
                                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Staff -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card bg-info text-white h-100 shadow">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase mb-1">Total Staff</div>
                                    <div class="h3 mb-0 fw-bold">48</div>
                                </div>
                                <div class="icon"><i class="fas fa-user-tie"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Fees -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card bg-warning text-white h-100 shadow">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase mb-1">Pending Fees</div>
                                    <div class="h3 mb-0 fw-bold">₹1,20,000</div>
                                </div>
                                <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Expenses -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card bg-danger text-white h-100 shadow">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase mb-1">Monthly Expenses</div>
                                    <div class="h3 mb-0 fw-bold">₹85,000</div>
                                </div>
                                <div class="icon"><i class="fas fa-wallet"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card mb-4">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold" style="color: #f39c12;">Revenue Overview</h6>
                                <div class="dropdown no-arrow">
                                    <a class="dropdown-toggle text-muted" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v fa-sm fa-fw"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <li><a class="dropdown-item" href="#">Action</a></li>
                                        <li><a class="dropdown-item" href="#">Another action</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-5">
                        <div class="card mb-4">
                            <div class="card-header bg-white py-3">
                                <h6 class="m-0 font-weight-bold" style="color: #f39c12;">Fee Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="feeDistributionChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Table -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h6 class="m-0 font-weight-bold" style="color: #f39c12;">Recent Activity</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Student Name</th>
                                                <th>Fee Type</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>John Doe</td>
                                                <td>Tuition Fee</td>
                                                <td>₹5,000</td>
                                                <td>2026-05-14</td>
                                                <td><span class="badge bg-success">Paid</span></td>
                                                <td><button class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></button></td>
                                            </tr>
                                            <tr>
                                                <td>Jane Smith</td>
                                                <td>Transport Fee</td>
                                                <td>₹1,200</td>
                                                <td>2026-05-13</td>
                                                <td><span class="badge bg-warning text-dark">Partial</span></td>
                                                <td><button class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></button></td>
                                            </tr>
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

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [30000, 45000, 38000, 52000, 48000, 60000],
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243, 156, 18, 0.05)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Fee Distribution Chart
        const ctx2 = document.getElementById('feeDistributionChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending', 'Overdue'],
                datasets: [{
                    data: [70, 20, 10],
                    backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Sidebar Toggle
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>

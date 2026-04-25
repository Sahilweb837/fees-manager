<?php
require_once '../includes/auth.php';
checkAccess(['super_admin']);  // Only god-level access
include '../includes/header.php';

// Global Stats
$total_revenue = $conn->query("SELECT SUM(amount) as total FROM fees WHERE status='paid'")->fetch_assoc()['total'] ?? 0;
$total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'] ?? 0;
$total_branches = $conn->query("SELECT COUNT(*) as total FROM branches")->fetch_assoc()['total'] ?? 0;
$total_staff = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='employee'")->fetch_assoc()['total'] ?? 0;

// Branch-wise distribution
$branch_stats = $conn->query("
    SELECT b.branch_name, COUNT(s.id) as student_count, SUM(f.amount) as revenue
    FROM branches b 
    LEFT JOIN students s ON s.branch_id = b.id 
    LEFT JOIN fees f ON f.student_id = s.id AND f.status = 'paid'
    GROUP BY b.id
    ORDER BY revenue DESC
");
?>

<div class="animate-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">Administrative Control Panel</h2>
            <p class="text-muted mb-0">Unified oversight of all branches and operations.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-dark p-2 px-3 rounded-pill shadow-sm">
                <i class="fas fa-shield-alt me-2 text-warning"></i>Privileged Access: <?php echo strtoupper($_SESSION['role']); ?>
            </span>
        </div>
    </div>

    <!-- Global Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-primary-gradient text-white">
                <div class="card-body p-4">
                    <div class="small text-uppercase fw-bold opacity-75 mb-1">Network Revenue</div>
                    <h3 class="fw-bold mb-0">₹<?php echo number_format($total_revenue); ?></h3>
                    <div class="mt-3 small"><i class="fas fa-globe-asia me-1"></i> Across all centers</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body p-4">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Total Enrollment</div>
                    <h3 class="fw-bold text-dark mb-0"><?php echo number_format($total_students); ?></h3>
                    <div class="mt-3 small text-success fw-bold"><i class="fas fa-users me-1"></i> Active Profiles</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body p-4">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Branches</div>
                    <h3 class="fw-bold text-dark mb-0"><?php echo number_format($total_branches); ?></h3>
                    <div class="mt-3 small text-primary fw-bold"><i class="fas fa-map-marked-alt me-1"></i> Active Locations</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body p-4">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Workforce</div>
                    <h3 class="fw-bold text-dark mb-0"><?php echo number_format($total_staff); ?></h3>
                    <div class="mt-3 small text-info fw-bold"><i class="fas fa-user-tie me-1"></i> System Staff</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Branch Performance -->
        <div class="col-lg-8">
            <div class="card glass-card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white p-4 border-0">
                    <h5 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Branch Performance Ledger</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="small text-muted text-uppercase">
                                    <th class="ps-4">Branch Center</th>
                                    <th>Students</th>
                                    <th class="pe-4 text-end">Collection</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($branch_stats && $branch_stats->num_rows > 0): ?>
                                    <?php while($bs = $branch_stats->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($bs['branch_name']); ?></td>
                                        <td><span class="badge bg-light text-dark border rounded-pill px-3"><?php echo $bs['student_count']; ?></span></td>
                                        <td class="pe-4 text-end fw-bold text-success">₹<?php echo number_format($bs['revenue'] ?? 0); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">No branch data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Admin Actions -->
        <div class="col-lg-4">
            <div class="card glass-card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white p-4 border-0">
                    <h5 class="fw-bold mb-0">Administrative Tasks</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="users.php" class="list-group-item list-group-item-action p-4">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Provision New Staff</h6>
                                <p class="mb-0 text-muted small">Create IDs and generate passwords.</p>
                            </div>
                            <i class="fas fa-user-plus text-primary"></i>
                        </div>
                    </a>
                    <a href="branches.php" class="list-group-item list-group-item-action p-4">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Center Management</h6>
                                <p class="mb-0 text-muted small">Add or update branch locations.</p>
                            </div>
                            <i class="fas fa-building text-info"></i>
                        </div>
                    </a>
                    <a href="logs.php" class="list-group-item list-group-item-action p-4">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">System Audit</h6>
                                <p class="mb-0 text-muted small">View all activity logs.</p>
                            </div>
                            <i class="fas fa-history text-warning"></i>
                        </div>
                    </a>
                    <a href="students.php" class="list-group-item list-group-item-action p-4">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Global Directory</h6>
                                <p class="mb-0 text-muted small">All entities across all branches.</p>
                            </div>
                            <i class="fas fa-users-viewfinder text-success"></i>
                        </div>
                    </a>
                    <a href="fees.php" class="list-group-item list-group-item-action p-4">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Global Revenue</h6>
                                <p class="mb-0 text-muted small">Consolidated ledger of all payments.</p>
                            </div>
                            <i class="fas fa-file-invoice-dollar text-primary"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

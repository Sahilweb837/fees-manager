<?php
require_once '../includes/auth.php';
include '../includes/header.php';

// Fetch Summary Stats
// 1. Total Students
$res = $conn->query("SELECT COUNT(*) as total FROM students");
$total_students = $res->fetch_assoc()['total'];

// 2. Total Revenue
$res = $conn->query("SELECT SUM(amount) as total FROM fees");
$total_revenue = $res->fetch_assoc()['total'] ?? 0;

// 3. Total Users (Employees/Admins)
$res = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $res->fetch_assoc()['total'];

// 4. Recent Activities
$recent_activities = $conn->query("
    SELECT a.*, u.username 
    FROM activity_logs a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="fw-bold">Dashboard Overview</h2>
        <p class="text-muted">Welcome back! Here's what's happening today.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title">Total Students</h5>
                    <h2 class="fw-bold"><?php echo $total_students; ?></h2>
                </div>
                <i class="fas fa-user-graduate fa-3x opacity-50"></i>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="students.php">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title">Total Revenue</h5>
                    <h2 class="fw-bold">₹<?php echo number_format($total_revenue, 2); ?></h2>
                </div>
                <i class="fas fa-rupee-sign fa-3x opacity-50"></i>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="fees.php">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

    <?php if ($_SESSION['role'] == 'super_admin' || $_SESSION['role'] == 'admin'): ?>
    <div class="col-md-4">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title">System Users</h5>
                    <h2 class="fw-bold"><?php echo $total_users; ?></h2>
                </div>
                <i class="fas fa-users-cog fa-3x opacity-50"></i>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-dark stretched-link" href="users.php">View Details</a>
                <div class="small text-dark"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-1"></i> Recent Activities
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $recent_activities->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['username']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['action']); ?></td>
                                <td><?php echo htmlspecialchars($row['details']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($recent_activities->num_rows == 0): ?>
                            <tr><td colspan="4" class="text-center">No recent activities found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

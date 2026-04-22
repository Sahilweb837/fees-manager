<?php
require_once '../includes/auth.php';
include '../includes/header.php';

// Fetch Recent Activities (Still server-side for initial load)
$recent_activities = $conn->query("
    SELECT a.*, u.username 
    FROM activity_logs a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 6
");
?>

<div class="animate-up">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold text-dark">Dashboard Overview</h2>
            <p class="text-muted">Welcome back, <span class="text-primary fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></span>! Here's your institute's summary.</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Students</h6>
                            <h2 class="fw-bold mb-0" id="stat-students">...</h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card bg-success text-white border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Revenue</h6>
                            <h2 class="fw-bold mb-0">₹<span id="stat-revenue">...</span></h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card bg-info text-white border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Today's Presence</h6>
                            <h2 class="fw-bold mb-0" id="stat-attendance">...</h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card bg-dark text-white border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Active Courses</h6>
                            <h2 class="fw-bold mb-0" id="stat-courses">...</h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-book-open fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activity -->
        <div class="col-lg-8">
            <div class="card glass-card border-0 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Recent Activity Logs</h5>
                    <a href="logs.php" class="btn btn-light btn-sm rounded-pill px-3">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4">Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th class="px-4">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $recent_activities->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-4 text-muted small"><?php echo date('h:i A', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2 bg-primary-subtle text-primary rounded-circle text-center" style="width: 30px; height: 30px; line-height: 30px;">
                                                <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                            </div>
                                            <span class="fw-semibold"><?php echo htmlspecialchars($row['username']); ?></span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['action']); ?></span></td>
                                    <td class="px-4 small text-muted"><?php echo htmlspecialchars($row['details']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="col-lg-4">
            <div class="card glass-card border-0">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Quick Actions</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-3">
                        <a href="students.php" class="btn btn-outline-primary text-start p-3 rounded-4">
                            <i class="fas fa-plus-circle me-2"></i> Add New Student
                        </a>
                        <a href="attendance.php" class="btn btn-outline-info text-start p-3 rounded-4">
                            <i class="fas fa-check-double me-2"></i> Mark Attendance
                        </a>
                        <a href="fees.php" class="btn btn-outline-success text-start p-3 rounded-4">
                            <i class="fas fa-rupee-sign me-2"></i> Collect Fees
                        </a>
                        <a href="courses.php" class="btn btn-outline-dark text-start p-3 rounded-4">
                            <i class="fas fa-graduation-cap me-2"></i> Manage Courses
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Lazy load stats
    fetch('get_stats.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('stat-students').innerText = data.students;
            document.getElementById('stat-revenue').innerText = data.revenue;
            document.getElementById('stat-attendance').innerText = data.attendance;
            document.getElementById('stat-courses').innerText = data.courses;
        })
        .catch(err => console.error('Error fetching stats:', err));
});
</script>

<?php include '../includes/footer.php'; ?>

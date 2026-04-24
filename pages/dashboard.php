<?php
require_once '../includes/auth.php';
include '../includes/header.php';

// Stats Queries
$total_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE status='active'")->fetch_assoc()['count'];
$total_courses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];

// Financial Metrics
$revenue_data = $conn->query("
    SELECT 
        SUM(total_fees) as total_expected,
        (SELECT SUM(amount) FROM fees) as total_collected
    FROM students
")->fetch_assoc();

$expected = $revenue_data['total_expected'] ?? 0;
$collected = $revenue_data['total_collected'] ?? 0;
$remaining = $expected - $collected;

$collection_rate = $expected > 0 ? ($collected / $expected) * 100 : 0;

// Recent Activity
$recent_logs = $conn->query("
    SELECT l.*, u.username 
    FROM activity_logs l 
    JOIN users u ON l.user_id = u.id 
    ORDER BY l.created_at DESC 
    LIMIT 6
");

// Recent Fees
$recent_fees = $conn->query("
    SELECT f.*, s.student_name 
    FROM fees f 
    JOIN students s ON f.student_id = s.id 
    ORDER BY f.date_collected DESC 
    LIMIT 6
");
?>

<div class="animate-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">Academy Dashboard</h2>
            <p class="text-muted mb-0">Financial health and enrollment overview.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary-subtle text-primary p-2 px-3 rounded-pill">
                <i class="fas fa-calendar-alt me-2"></i><?php echo date('F d, Y'); ?>
            </span>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="metric-card p-4 h-100">
                <div class="d-flex justify-content-between mb-3">
                    <div class="icon-box" style="background: rgba(255, 85, 50, 0.1); color: var(--first-color);">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1"><?php echo number_format($total_students); ?></h3>
                <p class="text-muted small mb-0 text-uppercase fw-bold" style="letter-spacing: 1px;">Active Students</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card p-4 h-100">
                <div class="d-flex justify-content-between mb-3">
                    <div class="icon-box" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                        <i class="fas fa-sack-dollar"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1">₹<?php echo number_format($collected); ?></h3>
                <p class="text-muted small mb-0 text-uppercase fw-bold" style="letter-spacing: 1px;">Fees Collected</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card p-4 h-100">
                <div class="d-flex justify-content-between mb-3">
                    <div class="icon-box" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1">₹<?php echo number_format($remaining); ?></h3>
                <p class="text-muted small mb-0 text-uppercase fw-bold" style="letter-spacing: 1px;">Pending Balance</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card p-4 h-100">
                <div class="d-flex justify-content-between mb-3">
                    <div class="icon-box" style="background: rgba(0, 123, 255, 0.1); color: #007bff;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1"><?php echo number_format($collection_rate, 1); ?>%</h3>
                <p class="text-muted small mb-0 text-uppercase fw-bold" style="letter-spacing: 1px;">Collection Rate</p>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar" style="width: <?php echo $collection_rate; ?>%; background: #007bff;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Collections -->
        <div class="col-lg-8">
            <div class="card glass-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Recent Collections</h5>
                        <a href="fees.php" class="btn btn-light btn-sm rounded-pill px-3">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th class="ps-0 border-0">Student</th>
                                    <th class="border-0">Type</th>
                                    <th class="text-end pe-0 border-0">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($fee = $recent_fees->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-0">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($fee['student_name']); ?></div>
                                        <small class="text-muted"><?php echo date('d M Y', strtotime($fee['date_collected'])); ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo ucfirst($fee['fee_type']); ?></span></td>
                                    <td class="text-end pe-0">
                                        <div class="fw-bold text-success">₹<?php echo number_format($fee['amount']); ?></div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Logs -->
        <div class="col-lg-4">
            <div class="card glass-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Recent Activity</h5>
                        <a href="logs.php" class="text-primary small text-decoration-none">Logs <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                    <div class="activity-timeline">
                        <?php while($log = $recent_logs->fetch_assoc()): ?>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle bg-light border" style="width: 10px; height: 10px; margin-top: 5px;"></div>
                            </div>
                            <div>
                                <div class="fw-bold small text-dark"><?php echo htmlspecialchars($log['action']); ?></div>
                                <div class="text-muted small" style="font-size: 11px;"><?php echo htmlspecialchars($log['details']); ?></div>
                                <small class="text-muted" style="font-size: 10px;"><?php echo date('h:i A', strtotime($log['created_at'])); ?> by <?php echo htmlspecialchars($log['username']); ?></small>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

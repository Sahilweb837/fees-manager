<?php
require_once '../includes/auth.php';
checkAccess(['super_admin', 'admin']);
include '../includes/header.php';

// Fetch Logs
$logs = $conn->query("
    SELECT a.*, u.username, u.role 
    FROM activity_logs a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.id DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Activity Logs</h2>
    <p class="text-muted mb-0">Track all actions performed by users.</p>
</div>

<div class="card border-dark">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable w-100">
                <thead class="table-dark">
                    <tr>
                        <th>Log ID</th>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $logs->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo date('d M Y, h:i:s A', strtotime($row['created_at'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                        <td>
                            <?php 
                                $r = $row['role'];
                                $badge = 'bg-secondary';
                                if($r == 'super_admin') $badge = 'bg-danger';
                                else if($r == 'admin') $badge = 'bg-warning text-dark';
                                else if($r == 'employee') $badge = 'bg-info text-dark';
                                echo "<span class='badge $badge'>" . strtoupper(str_replace('_', ' ', $r)) . "</span>";
                            ?>
                        </td>
                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($row['action']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['details']); ?></td>
                        <td><small class="text-muted"><?php echo htmlspecialchars($row['ip_address']); ?></small></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

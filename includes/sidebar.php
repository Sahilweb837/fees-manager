        <!-- Sidebar -->
        <div class="sidebar-wrapper bg-dark border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 primary-text fs-4 fw-bold text-uppercase border-bottom">
                <i class="fas fa-graduation-cap me-2"></i>FMS
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="../pages/dashboard.php" class="list-group-item list-group-item-action bg-transparent second-text fw-bold">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                
                <?php if ($_SESSION['role'] == 'super_admin' || $_SESSION['role'] == 'admin'): ?>
                <a href="../pages/users.php" class="list-group-item list-group-item-action bg-transparent second-text fw-bold">
                    <i class="fas fa-users-cog me-2"></i>Manage Users
                </a>
                <?php endif; ?>

                <a href="../pages/students.php" class="list-group-item list-group-item-action bg-transparent second-text fw-bold">
                    <i class="fas fa-user-graduate me-2"></i>Students
                </a>
                <a href="../pages/fees.php" class="list-group-item list-group-item-action bg-transparent second-text fw-bold">
                    <i class="fas fa-money-bill-wave me-2"></i>Fees Collection
                </a>

                <?php if ($_SESSION['role'] == 'super_admin' || $_SESSION['role'] == 'admin'): ?>
                <a href="../pages/logs.php" class="list-group-item list-group-item-action bg-transparent second-text fw-bold">
                    <i class="fas fa-history me-2"></i>Activity Logs
                </a>
                <?php endif; ?>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

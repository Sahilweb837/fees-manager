<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar-wrapper" id="sidebar-wrapper">
    <div class="sidebar-heading">
        <h3 class="fw-bold text-white mb-0">FMS<span style="color: var(--first-color);">PRO</span></h3>
        <small class="text-muted text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 2px;">Fees Manager</small>
    </div>
    <div class="list-group list-group-flush my-3">
        <a href="../pages/dashboard.php" class="list-group-item list-group-item-action <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-grid-2"></i>Dashboard
        </a>
        
        <?php if ($_SESSION['role'] == 'super_admin' || $_SESSION['role'] == 'admin'): ?>
        <a href="../pages/users.php" class="list-group-item list-group-item-action <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users-gear"></i>Manage Users
        </a>
        <?php endif; ?>

        <a href="../pages/courses.php" class="list-group-item list-group-item-action <?php echo $current_page == 'courses.php' ? 'active' : ''; ?>">
            <i class="fas fa-book-open"></i>Courses
        </a>

        <a href="../pages/students.php" class="list-group-item list-group-item-action <?php echo $current_page == 'students.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-graduate"></i>Students
        </a>

        <a href="../pages/attendance.php" class="list-group-item list-group-item-action <?php echo $current_page == 'attendance.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>Attendance
        </a>

        <a href="../pages/fees.php" class="list-group-item list-group-item-action <?php echo $current_page == 'fees.php' ? 'active' : ''; ?>">
            <i class="fas fa-wallet"></i>Fees Collection
        </a>

        <?php if ($_SESSION['role'] == 'super_admin' || $_SESSION['role'] == 'admin'): ?>
        <a href="../pages/logs.php" class="list-group-item list-group-item-action <?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">
            <i class="fas fa-shield-halved"></i>Activity Logs
        </a>
        <?php endif; ?>
    </div>
</div>
<!-- /#sidebar-wrapper -->

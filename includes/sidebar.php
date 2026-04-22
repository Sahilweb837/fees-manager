<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar-wrapper" id="sidebar-wrapper">
    <div class="sidebar-heading text-center py-4">
        <img src="https://www.netcoder.in/images/logo.png" alt="NETCODER" style="width: 150px; filter: brightness(0) invert(1);">
    </div>
    <div class="list-group list-group-flush my-3">
        <a href="../pages/dashboard.php" class="list-group-item list-group-item-action <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>Dashboard
        </a>
        
        <?php if ($_SESSION['role'] == 'super_admin' || $_SESSION['role'] == 'admin'): ?>
        <a href="../pages/users.php" class="list-group-item list-group-item-action <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users-cog"></i>Manage Users
        </a>
        <?php endif; ?>

        <a href="../pages/courses.php" class="list-group-item list-group-item-action <?php echo $current_page == 'courses.php' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i>Courses
        </a>

        <a href="../pages/students.php" class="list-group-item list-group-item-action <?php echo $current_page == 'students.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-graduate"></i>Students
        </a>

        <a href="../pages/attendance.php" class="list-group-item list-group-item-action <?php echo $current_page == 'attendance.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>Attendance
        </a>

        <a href="../pages/fees.php" class="list-group-item list-group-item-action <?php echo $current_page == 'fees.php' ? 'active' : ''; ?>">
            <i class="fas fa-money-bill-wave"></i>Fees Collection
        </a>

        <?php if ($_SESSION['role'] == 'super_admin' || $_SESSION['role'] == 'admin'): ?>
        <a href="../pages/logs.php" class="list-group-item list-group-item-action <?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i>Activity Logs
        </a>
        <?php endif; ?>
    </div>
</div>
<!-- /#sidebar-wrapper -->

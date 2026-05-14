<?php
// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<nav id="sidebar">
    <div class="sidebar-header d-flex align-items-center justify-content-center py-4">
        <i class="fa fa-university fa-2x me-2"></i>
        <h4 class="m-0 fw-bold">ERP Pro</h4>
    </div>

    <ul class="list-unstyled components">
        <li class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php"><i class="fa fa-home me-2"></i> Dashboard</a>
        </li>
        <li class="<?php echo $current_page == 'students.php' ? 'active' : ''; ?>">
            <a href="students.php"><i class="fa fa-user-graduate me-2"></i> Interns/Students</a>
        </li>
        <li class="<?php echo $current_page == 'attendance.php' ? 'active' : ''; ?>">
            <a href="attendance.php"><i class="fa fa-calendar-check me-2"></i> Attendance</a>
        </li>
        <li class="<?php echo $current_page == 'staff.php' ? 'active' : ''; ?>">
            <a href="staff.php"><i class="fa fa-users me-2"></i> Staff</a>
        </li>
        <li class="<?php echo $current_page == 'fees.php' ? 'active' : ''; ?>">
            <a href="fees.php"><i class="fa fa-credit-card me-2"></i> Fees & Payments</a>
        </li>
        <li class="<?php echo $current_page == 'expenses.php' ? 'active' : ''; ?>">
            <a href="expenses.php"><i class="fa fa-wallet me-2"></i> Expenses</a>
        </li>
        <li class="<?php echo $current_page == 'files.php' ? 'active' : ''; ?>">
            <a href="files.php"><i class="fa fa-folder-open me-2"></i> Files</a>
        </li>
        <li class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <a href="reports.php"><i class="fa fa-chart-line me-2"></i> Reports</a>
        </li>
        <li class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <a href="users.php"><i class="fa fa-user-shield me-2"></i> Users</a>
        </li>
        <li class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <a href="settings.php"><i class="fa fa-cog me-2"></i> Settings</a>
        </li>
        <li>
            <a href="../logout.php" class="text-danger"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
        </li>
    </ul>
</nav>

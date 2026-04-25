<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));
$btype = $_SESSION['business_type'] ?? 'other';
$role  = $_SESSION['role'] ?? 'employee';

// Industry-aware labels and icons
$industry_config = [
    'school'     => ['label' => 'School ERP',   'icon' => 'fa-school',       'entity' => 'Students',   'color' => '#3b82f6'],
    'college'    => ['label' => 'College ERP',  'icon' => 'fa-university',   'entity' => 'Students',   'color' => '#6366f1'],
    'restaurant' => ['label' => 'Restaurant',   'icon' => 'fa-utensils',     'entity' => 'Orders',     'color' => '#ef4444'],
    'hotel'      => ['label' => 'Hotel Mgmt',   'icon' => 'fa-hotel',        'entity' => 'Guests',     'color' => '#8b5cf6'],
    'shop'       => ['label' => 'Shop POS',     'icon' => 'fa-store',        'entity' => 'Customers',  'color' => '#f59e0b'],
    'dispensary' => ['label' => 'Clinic Mgmt',  'icon' => 'fa-clinic-medical','entity' => 'Patients',  'color' => '#14b8a6'],
    'inventory'  => ['label' => 'Inventory',    'icon' => 'fa-boxes-stacked','entity' => 'Clients',   'color' => '#10b981'],
    'other'      => ['label' => 'FMS Pro',      'icon' => 'fa-briefcase',    'entity' => 'Clients',   'color' => '#ff7a00'],
];
$icfg = $industry_config[$btype] ?? $industry_config['other'];

// Determine path prefix (are we in subfolder or pages/ ?)
$in_subfolder = in_array($current_dir, ['school','college','restaurant','hotel','shop','dispensary','inventory','admin','pages','staff']);
$prefix = $in_subfolder ? '../' : './';
?>
<!-- Sidebar -->
<div class="sidebar-wrapper" id="sidebar-wrapper">
    <div class="sidebar-heading">
        <div class="d-flex align-items-center gap-2">
            <div style="width:36px;height:36px;border-radius:10px;background:var(--first-color);display:flex;align-items:center;justify-content:center;">
                <i class="fas <?php echo $icfg['icon']; ?> text-white" style="font-size:1rem;"></i>
            </div>
            <div>
                <div class="fw-bold text-white lh-1" style="font-size:0.95rem;">FMS<span style="color:var(--first-color);">PRO</span></div>
                <div class="text-muted" style="font-size:9px;letter-spacing:1.5px;text-transform:uppercase;"><?php echo $icfg['label']; ?></div>
            </div>
        </div>
    </div>
    <div class="list-group list-group-flush my-2">

        <?php /* ── SUPER ADMIN LINKS ── */ if ($role === 'super_admin'): ?>
        <div class="sidebar-section-label">Global Control</div>
        <a href="<?php echo $prefix; ?>admin/index.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'index.php' && $current_dir == 'admin') ? 'active' : ''; ?>">
            <i class="fas fa-shield-halved"></i>Admin Dashboard
        </a>
        <a href="<?php echo $prefix; ?>admin/branches.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'branches.php') ? 'active' : ''; ?>">
            <i class="fas fa-building"></i>Manage Branches
        </a>
        <a href="<?php echo $prefix; ?>admin/users.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'users.php' && $current_dir == 'admin') ? 'active' : ''; ?>">
            <i class="fas fa-users-gear"></i>System Users
        </a>
        <a href="<?php echo $prefix; ?>admin/students.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'students.php' && $current_dir == 'admin') ? 'active' : ''; ?>">
            <i class="fas fa-users-viewfinder"></i>All Records
        </a>
        <a href="<?php echo $prefix; ?>admin/fees.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'fees.php' && $current_dir == 'admin') ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>Global Revenue
        </a>
        <a href="<?php echo $prefix; ?>admin/logs.php" class="list-group-item list-group-item-action <?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i>Audit Logs
        </a>

        <?php /* ── BRANCH ADMIN LINKS ── */ elseif ($role === 'admin'): ?>
        <div class="sidebar-section-label"><?php echo $icfg['label']; ?></div>
        <a href="<?php echo $prefix . $btype; ?>/dashboard.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-gauge-high"></i>Dashboard
        </a>
        <a href="<?php echo $prefix . $btype; ?>/members.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'members.php') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i><?php echo $icfg['entity']; ?>
        </a>
        <a href="<?php echo $prefix . $btype; ?>/payments.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>">
            <i class="fas fa-wallet"></i>Payments
        </a>
        <div class="sidebar-section-label">Administration</div>
        <a href="<?php echo $prefix . $btype; ?>/users.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i>My Staff
        </a>
        <a href="<?php echo $prefix . $btype; ?>/courses.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'courses.php') ? 'active' : ''; ?>">
            <i class="fas fa-book-open"></i>
            <?php echo in_array($btype, ['restaurant','hotel']) ? 'Services/Menus' : (($btype == 'shop') ? 'Products' : 'Courses'); ?>
        </a>
        <a href="<?php echo $prefix; ?>pages/expenses.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'expenses.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar"></i>Expenses
        </a>
        <a href="<?php echo $prefix; ?>pages/attendance.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>Attendance
        </a>

        <?php /* ── EMPLOYEE LINKS ── */ else: ?>
        <div class="sidebar-section-label">My Panel</div>
        <a href="<?php echo $prefix . $btype; ?>/dashboard.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-gauge-high"></i>Dashboard
        </a>
        <a href="<?php echo $prefix . $btype; ?>/members.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'members.php') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i><?php echo $icfg['entity']; ?>
        </a>
        <a href="<?php echo $prefix . $btype; ?>/payments.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>">
            <i class="fas fa-wallet"></i>Collect Payment
        </a>
        <a href="<?php echo $prefix; ?>pages/attendance.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>Attendance
        </a>
        <?php endif; ?>

    </div>
</div>

<style>
.sidebar-section-label {
    padding: 10px 24px 4px;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(255,255,255,0.25);
    font-weight: 700;
}
.sidebar-wrapper { min-height: 100vh; width: 260px; background: #0f172a !important; }
.sidebar-wrapper .list-group-item {
    background: transparent !important;
    color: #94a3b8 !important;
    padding: 11px 24px;
    border: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
    border-radius: 10px;
    margin: 2px 12px;
    font-size: 0.875rem;
    transition: all 0.18s;
}
.sidebar-wrapper .list-group-item i { font-size: 1rem; width: 18px; opacity: 0.7; }
.sidebar-wrapper .list-group-item:hover { color: #fff !important; background: rgba(255,255,255,0.04) !important; }
.sidebar-wrapper .list-group-item.active {
    color: var(--first-color) !important;
    background: linear-gradient(90deg, rgba(var(--bs-primary-rgb),0.12), transparent) !important;
    border-left: 3px solid var(--first-color);
}
.sidebar-wrapper .list-group-item.active i { opacity: 1; color: var(--first-color); }
.sidebar-heading { padding: 1.5rem 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
</style>

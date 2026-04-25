<?php
$current_page  = basename($_SERVER['PHP_SELF']);
$btype         = $_SESSION['business_type'] ?? 'other';

$industry_colors = [
    'school'     => ['primary' => '#3b82f6', 'name' => 'School ERP'],
    'college'    => ['primary' => '#6366f1', 'name' => 'College ERP'],
    'dispensary' => ['primary' => '#14b8a6', 'name' => 'Clinic'],
    'hotel'      => ['primary' => '#8b5cf6', 'name' => 'Hotel'],
    'shop'       => ['primary' => '#f59e0b', 'name' => 'Shop'],
    'restaurant' => ['primary' => '#ef4444', 'name' => 'Restaurant'],
    'inventory'  => ['primary' => '#10b981', 'name' => 'Inventory'],
    'company'    => ['primary' => '#64748b', 'name' => 'Company'],
    'other'      => ['primary' => '#ff7a00', 'name' => 'FMS Pro'],
];
$ic     = $industry_colors[$btype] ?? $industry_colors['other'];
$accent = $ic['primary'];

function adjustBrightness($hex, $steps) {
    $steps = max(-255, min(255, $steps));
    $hex   = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex,0,1),2).str_repeat(substr($hex,1,1),2).str_repeat(substr($hex,2,1),2);
    }
    $r = max(0,min(255,hexdec(substr($hex,0,2))+$steps));
    $g = max(0,min(255,hexdec(substr($hex,2,2))+$steps));
    $b = max(0,min(255,hexdec(substr($hex,4,2))+$steps));
    return '#'.str_pad(dechex($r),2,'0',STR_PAD_LEFT).str_pad(dechex($g),2,'0',STR_PAD_LEFT).str_pad(dechex($b),2,'0',STR_PAD_LEFT);
}
$accent_hover = adjustBrightness($accent, -25);
$accent_light = $accent.'20';
// Convert hex to R,G,B for Bootstrap rgb vars
list($r,$g,$b) = sscanf($accent, "#%02x%02x%02x");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $ic['name']; ?> — FMS Enterprise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --first-color:       <?php echo $accent; ?>;
            --first-color-alt:   <?php echo $accent_hover; ?>;
            --first-color-light: <?php echo $accent_light; ?>;
            --bs-primary:        <?php echo $accent; ?>;
            --bs-primary-rgb:    <?php echo "$r,$g,$b"; ?>;
            --border-color:      #f1f5f9;
            --shadow:            0 4px 16px -4px rgba(0,0,0,0.08);
            --body-bg:           #f8fafc;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--body-bg); color: #1a1a2e; overflow-x: hidden; }

        /* Color overrides */
        .text-primary  { color: var(--first-color) !important; }
        .bg-primary    { background-color: var(--first-color) !important; }
        .bg-primary-subtle { background-color: var(--first-color-light) !important; }
        .btn-primary   { background: var(--first-color) !important; border-color: var(--first-color) !important; color: #fff !important; font-weight: 600; }
        .btn-primary:hover { background: var(--first-color-alt) !important; border-color: var(--first-color-alt) !important; }
        .btn-outline-primary { color: var(--first-color) !important; border-color: var(--first-color) !important; }
        .btn-outline-primary:hover { background: var(--first-color) !important; color: #fff !important; }
        .border-primary { border-color: var(--first-color) !important; }
        .badge.bg-primary { background: var(--first-color) !important; }

        /* Layout */
        .wrapper { display: flex; width: 100%; min-height: 100vh; }
        #page-content-wrapper { min-width: 0; width: 100%; }

        /* Sidebar */
        .sidebar-wrapper { transition: margin 0.25s ease; }
        @media (max-width: 768px) {
            .sidebar-wrapper { margin-left: -260px; position: fixed; z-index: 1050; height: 100%; }
            .wrapper.toggled .sidebar-wrapper { margin-left: 0; }
        }
        .wrapper, .sidebar-wrapper, #page-content-wrapper { transition: all 0.25s ease; }

        /* Navbar */
        .top-navbar { background: #fff; border-bottom: 1px solid var(--border-color); padding: 0.7rem 1.5rem; position: sticky; top: 0; z-index: 100; }

        /* Cards */
        .glass-card { background: #fff; border: 1px solid var(--border-color); border-radius: 16px; box-shadow: var(--shadow); }
        .metric-card { background: #fff; border: 1px solid var(--border-color); border-radius: 14px; box-shadow: var(--shadow); }
        .card { border: 1px solid var(--border-color); border-radius: 16px; box-shadow: var(--shadow); background: #fff; }

        /* Icon boxes */
        .icon-box { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }

        /* Tables */
        .table thead th { background: #f8fafc; color: #64748b; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); }
        .table tbody tr td { padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table-hover tbody tr:hover { background-color: #f8fafc !important; }

        /* Buttons */
        .btn { font-weight: 600; border-radius: 10px; font-size: 0.85rem; padding: 8px 18px; }
        .btn-light { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; }
        .btn-sm { padding: 5px 12px; font-size: 0.8rem; }

        /* Badges */
        .badge { padding: 5px 12px; font-weight: 600; border-radius: 6px; font-size: 10px; letter-spacing: 0.03em; }

        /* Loader */
        #preloader { position: fixed; inset: 0; background: #fff; z-index: 10000; display: flex; align-items: center; justify-content: center; }
        .loader-ring { width: 42px; height: 42px; border: 3px solid #f1f5f9; border-top-color: var(--first-color); border-radius: 50%; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Animations */
        .animate-up { animation: fadeUp 0.4s ease; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        /* Misc */
        .main-container { padding: 1.5rem 2rem; }
        h1,h2,h3,h4,h5,h6 { font-weight: 700; letter-spacing: -0.02em; }
        .form-control, .form-select { border-radius: 10px; border-color: var(--border-color); font-size: 0.9rem; }
        .form-control:focus, .form-select:focus { border-color: var(--first-color); box-shadow: 0 0 0 3px var(--first-color-light); }
        .form-label { font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        .modal-content { border: none; border-radius: 16px; }
        .input-group .form-control { border-radius: 0 10px 10px 0 !important; }
        .input-group .input-group-text { border-radius: 10px 0 0 10px !important; background: #f8fafc; border-color: var(--border-color); }
        .sidebar-wrapper .list-group-item.active { background: linear-gradient(90deg, var(--first-color-light), transparent) !important; color: var(--first-color) !important; border-left: 3px solid var(--first-color); }
        .sidebar-wrapper .list-group-item.active i { color: var(--first-color) !important; }
    </style>
</head>
<body>
<div id="preloader"><div class="loader-ring"></div></div>
<div class="d-flex wrapper">
    <?php include 'sidebar.php'; ?>
    <div id="page-content-wrapper" class="w-100">
        <!-- Top Navbar -->
        <nav class="top-navbar d-flex align-items-center">
            <button class="btn btn-light btn-sm me-3 border" id="menu-toggle"><i class="fas fa-bars"></i></button>

            <div class="d-none d-md-flex position-relative me-3" style="width:240px;">
                <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted" style="font-size:0.8rem;"></i>
                <input class="form-control form-control-sm ps-5 bg-light border-0" type="search" placeholder="Search..." id="globalSearch">
            </div>

            <div class="ms-auto d-flex align-items-center gap-2">
                <span class="badge d-none d-sm-inline-flex" style="background:var(--first-color-light);color:var(--first-color);font-size:0.7rem;padding:6px 12px;">
                    <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($_SESSION['branch_name'] ?? $ic['name']); ?>
                </span>

                <div class="dropdown">
                    <button class="btn btn-light border btn-sm d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="dropdown">
                        <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-circle fw-bold" style="width:28px;height:28px;font-size:11px;">
                            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        </div>
                        <div class="text-start d-none d-sm-block">
                            <div class="fw-bold" style="font-size:0.8rem;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                            <div class="text-muted" style="font-size:0.65rem;"><?php echo strtoupper(str_replace('_',' ',$_SESSION['role'])); ?></div>
                        </div>
                        <i class="fas fa-chevron-down text-muted" style="font-size:0.65rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 p-2 rounded-3" style="min-width:180px;">
                        <li class="px-3 pb-2 pt-1 border-bottom mb-1">
                            <div class="fw-bold small"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></div>
                            <div class="text-muted" style="font-size:0.7rem;"><?php echo htmlspecialchars($_SESSION['employee_id'] ?? ''); ?></div>
                        </li>
                        <li><a class="dropdown-item rounded-2 py-2 small" href="#"><i class="fas fa-user-circle me-2 text-muted"></i>My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item rounded-2 py-2 small text-danger" href="<?php echo strpos($_SERVER['PHP_SELF'],'/admin/') !== false || strpos($_SERVER['PHP_SELF'],'/pages/') !== false || strpos($_SERVER['PHP_SELF'],'/staff/') !== false ? '../' : ''; ?>pages/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="main-container">

<script>
window.addEventListener('load', () => { document.getElementById('preloader').style.display = 'none'; });
document.getElementById('menu-toggle').addEventListener('click', () => document.querySelector('.wrapper').classList.toggle('toggled'));
</script>

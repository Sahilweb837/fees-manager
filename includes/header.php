<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Simple Professional Loader -->
    <div id="preloader">
        <div class="loader-inner"></div>
    </div>

    <div class="d-flex wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div id="page-content-wrapper" class="w-100">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 sticky-top shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-light rounded-circle border me-3" id="menu-toggle"><i class="fas fa-bars"></i></button>
                    
                    <form class="d-none d-md-flex ms-2 position-relative w-25">
                        <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input class="form-control ps-5 border bg-light shadow-none" type="search" placeholder="Search..." aria-label="Search" id="globalSearch">
                    </form>

                    <div class="ms-auto d-flex align-items-center gap-3">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="handleRefresh(this)">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>

                        <div class="dropdown">
                            <button class="btn btn-white border rounded-pill px-3 py-1 d-flex align-items-center shadow-sm" type="button" data-bs-toggle="dropdown">
                                <div class="avatar-xs bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;">
                                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                </div>
                                <div class="text-start me-2 d-none d-sm-block">
                                    <div class="fw-bold small lh-1"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                                    <small class="text-muted" style="font-size: 9px;"><?php echo strtoupper(str_replace('_', ' ', $_SESSION['role'])); ?></small>
                                </div>
                                <i class="fas fa-chevron-down x-small text-muted" style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border shadow-lg mt-2 p-2 rounded-3">
                                <li><a class="dropdown-item rounded-2 py-2 small" href="#"><i class="fas fa-user-circle me-2 text-muted"></i>Profile</a></li>
                                <li><a class="dropdown-item rounded-2 py-2 small" href="#"><i class="fas fa-cog me-2 text-muted"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item rounded-2 py-2 small text-danger" href="../pages/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <script>
                window.addEventListener('load', function() {
                    document.getElementById('preloader').style.display = 'none';
                });

                // Professional Refresh
                function handleRefresh(btn) {
                    const icon = btn.querySelector('i');
                    icon.classList.add('fa-spin');
                    btn.disabled = true;
                    setTimeout(() => { window.location.reload(); }, 500);
                }

                // Sidebar Toggle Logic
                document.getElementById('menu-toggle').addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector('.wrapper').classList.toggle('toggled');
                });
            </script>
            <div class="container-fluid p-4 main-container">

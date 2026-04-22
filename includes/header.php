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
    <div class="d-flex wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div id="page-content-wrapper" class="w-100">
            <nav class="navbar navbar-expand-lg navbar-light glass-header border-bottom py-3 sticky-top">
                <div class="container-fluid">
                    <button class="btn btn-light rounded-circle shadow-sm me-3" id="menu-toggle"><i class="fas fa-bars"></i></button>
                    
                    <form class="d-none d-md-flex ms-2 position-relative w-25">
                        <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input class="form-control rounded-pill ps-5 border-0 bg-light shadow-none" type="search" placeholder="Search students, fees..." aria-label="Search" id="globalSearch">
                    </form>

                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn btn-light rounded-pill px-3 py-2 d-flex align-items-center shadow-sm border" type="button" data-bs-toggle="dropdown">
                                <div class="avatar-xs bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                </div>
                                <div class="text-start me-2 d-none d-sm-block">
                                    <div class="fw-bold small lh-1"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                                    <small class="text-muted" style="font-size: 10px;"><?php echo strtoupper(str_replace('_', ' ', $_SESSION['role'])); ?></small>
                                </div>
                                <i class="fas fa-chevron-down small text-muted"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 p-2 rounded-4">
                                <li><a class="dropdown-item rounded-3 py-2" href="#"><i class="fas fa-user-circle me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item rounded-3 py-2" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item rounded-3 py-2 text-danger" href="../pages/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            <div class="container-fluid p-4 main-container">

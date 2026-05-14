<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 px-4 mb-4">
    <div class="container-fluid">
        <button type="button" id="sidebarCollapse" class="btn btn-outline-primary border-0">
            <i class="fa fa-align-left"></i>
        </button>
        
        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                    <img src="../assets/img/default.png" class="rounded-circle me-2" width="35" height="35" alt="User">
                    <span class="fw-semibold text-dark"><?php echo $_SESSION['name']; ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                    <li><a class="dropdown-item" href="profile.php"><i class="fa fa-user me-2 text-muted"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="fa fa-cog me-2 text-muted"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fa fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

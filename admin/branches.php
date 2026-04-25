<?php
require_once '../includes/auth.php';
checkAccess(['super_admin']);  // Only god-level access
include '../includes/header.php';

$message = "";

// Handle Add Branch
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = $_POST['branch_name'];
    $type = $_POST['business_type'];
    $location = $_POST['location'];
    
    $stmt = $conn->prepare("INSERT INTO branches (branch_name, business_type, location) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $type, $location);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], "Add Branch", "Created $type branch: $name.");
        $message = "<div class='alert alert-success border-0 shadow HUD-alert animate-up'><i class='fas fa-check-circle me-2'></i> $type branch established.</div>";
    } else {
        $message = "<div class='alert alert-danger border-0 shadow HUD-alert animate-up'>Error: " . $conn->error . "</div>";
    }
}

// Handle Delete Branch
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($conn->query("DELETE FROM branches WHERE id = $id")) {
        logActivity($conn, $_SESSION['user_id'], "Delete Branch", "Removed branch ID: $id.");
        $message = "<div class='alert alert-success border-0 shadow HUD-alert animate-up'><i class='fas fa-trash-alt me-2'></i> Branch record removed.</div>";
    }
}

$branches = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
?>

<div class="animate-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">Business Network</h2>
            <p class="text-muted mb-0">Manage and categorize your centers and businesses.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addBranchModal">
            <i class="fas fa-plus-circle me-2"></i>New Registration
        </button>
    </div>

    <?php echo $message; ?>

    <div class="row g-4">
        <?php if ($branches && $branches->num_rows > 0): ?>
            <?php while($b = $branches->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card glass-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-primary-gradient text-white shadow-sm" style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <?php 
                                    $icon = 'fa-building';
                                    if($b['business_type'] == 'school') $icon = 'fa-school';
                                    else if($b['business_type'] == 'college') $icon = 'fa-university';
                                    else if($b['business_type'] == 'hotel') $icon = 'fa-hotel';
                                    else if($b['business_type'] == 'restaurant') $icon = 'fa-utensils';
                                    else if($b['business_type'] == 'shop') $icon = 'fa-store';
                                    else if($b['business_type'] == 'dispensary') $icon = 'fa-clinic-medical';
                                    else if($b['business_type'] == 'inventory') $icon = 'fa-boxes-stacked';
                                ?>
                                <i class="fas <?php echo $icon; ?> h5 mb-0"></i>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                    <li><a class="dropdown-item text-danger" href="?delete=<?php echo $b['id']; ?>" onclick="return confirm('Delete this branch?');"><i class="fas fa-trash-alt me-2"></i>Delete</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <h5 class="fw-bold text-dark mb-0 me-2"><?php echo htmlspecialchars($b['branch_name']); ?></h5>
                            <span class="badge bg-primary-subtle text-primary border-primary rounded-pill small" style="font-size: 10px;"><?php echo strtoupper($b['business_type']); ?></span>
                        </div>
                        <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i><?php echo htmlspecialchars($b['location'] ?? 'Location not specified'); ?></p>
                        
                        <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark border rounded-pill px-3">Active Unit</span>
                            <a href="users.php?branch=<?php echo $b['id']; ?>" class="btn btn-link btn-sm text-primary p-0 text-decoration-none">Staff Panel <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted mb-3"><i class="fas fa-layer-group fa-3x"></i></div>
                <h5 class="text-muted">No branches found. Start by adding your first center.</h5>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Branch Modal -->
<div class="modal fade" id="addBranchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 shadow-lg rounded-4">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-plus-circle me-2 text-primary"></i>Register Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Branch / Business Name</label>
                        <input type="text" name="branch_name" class="form-control rounded-3" placeholder="e.g. City High School" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Business Category</label>
                        <select name="business_type" class="form-select rounded-3" required>
                            <option value="school">School</option>
                            <option value="college">College</option>
                            <option value="company">Company / Office</option>
                            <option value="shop">Shop / Retail</option>
                            <option value="hotel">Hotel / Residency</option>
                            <option value="restaurant">Restaurant / Cafe</option>
                            <option value="dispensary">Dispensary / Clinic</option>
                            <option value="inventory">Business Inventory</option>
                            <option value="other">Other Business</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Location / Address</label>
                        <textarea name="location" class="form-control rounded-3" rows="3" placeholder="Full address of the center"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow">Add Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

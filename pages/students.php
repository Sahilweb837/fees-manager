<?php
require_once '../includes/auth.php';
include '../includes/header.php';

$message = "";

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = $_POST['student_name'];
    $father = $_POST['father_name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $college = $_POST['college'];
    $course_id = $_POST['course_id'];
    $duration = $_POST['duration'];
    
    $stmt = $conn->prepare("INSERT INTO students (student_name, father_name, contact, email, college, course_id, duration, added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssisi", $name, $father, $contact, $email, $college, $course_id, $duration, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $sid = $conn->insert_id;
        logActivity($conn, $_SESSION['user_id'], "Add Student", "Added student: $name (ID: $sid).");
        $message = "<div class='alert alert-success'>Student added successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error adding student: " . $conn->error . "</div>";
    }
}

// Handle Edit Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $sid = $_POST['student_id'];
    $name = $_POST['student_name'];
    $father = $_POST['father_name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $college = $_POST['college'];
    $course_id = $_POST['course_id'];
    $duration = $_POST['duration'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE students SET student_name=?, father_name=?, contact=?, email=?, college=?, course_id=?, duration=?, status=? WHERE id=?");
    $stmt->bind_param("sssssissi", $name, $father, $contact, $email, $college, $course_id, $duration, $status, $sid);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], "Edit Student", "Updated student: $name (ID: $sid).");
        $message = "<div class='alert alert-success'>Student updated successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating student.</div>";
    }
}

// Handle Delete Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $sid = $_POST['student_id'];
    
    // First get student name for logging
    $stmt = $conn->prepare("SELECT student_name FROM students WHERE id = ?");
    $stmt->bind_param("i", $sid);
    $stmt->execute();
    $s_data = $stmt->get_result()->fetch_assoc();
    $name = $s_data['student_name'] ?? 'Unknown';

    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $sid);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], "Delete Student", "Deleted student: $name (ID: $sid).");
        $message = "<div class='alert alert-success border-0 shadow-sm'>Student and all associated records deleted successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger border-0 shadow-sm'>Error deleting student. This may be due to existing records.</div>";
    }
}

// Fetch Students
$students = $conn->query("
    SELECT s.*, c.course_name, u.username as added_by_name 
    FROM students s 
    LEFT JOIN courses c ON s.course_id = c.id
    LEFT JOIN users u ON s.added_by = u.id 
    ORDER BY s.id DESC
");

// Fetch Courses for Dropdown
$courses_res = $conn->query("SELECT * FROM courses ORDER BY course_name ASC");
$course_list = [];
while($c = $courses_res->fetch_assoc()) { $course_list[] = $c; }
?>

<div class="animate-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">Manage Students</h2>
            <p class="text-muted">Register and manage student profiles and durations.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="fas fa-user-plus me-2"></i>Add New Student
        </button>
    </div>

    <?php echo $message; ?>

    <div class="card glass-card border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student Details</th>
                            <th>College & Email</th>
                            <th>Course / Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                <small class="text-muted">S/o: <?php echo htmlspecialchars($row['father_name']); ?></small><br>
                                <small class="text-primary"><i class="fas fa-phone-alt me-1"></i><?php echo htmlspecialchars($row['contact']); ?></small>
                            </td>
                            <td>
                                <div class="text-secondary small"><i class="fas fa-university me-1"></i><?php echo htmlspecialchars($row['college']); ?></div>
                                <div class="text-muted small"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($row['email']); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['course_name'] ?? 'N/A'); ?></span><br>
                                <span class="badge bg-info text-white mt-1"><?php echo str_replace('_', ' ', $row['duration']); ?></span>
                            </td>
                            <td>
                                <?php if($row['status'] == 'active'): ?>
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3">Active</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-outline-warning btn-sm edit-btn" 
                                        data-id="<?php echo $row['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['student_name']); ?>"
                                        data-father="<?php echo htmlspecialchars($row['father_name']); ?>"
                                        data-contact="<?php echo htmlspecialchars($row['contact']); ?>"
                                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                        data-college="<?php echo htmlspecialchars($row['college']); ?>"
                                        data-course="<?php echo $row['course_id']; ?>"
                                        data-duration="<?php echo $row['duration']; ?>"
                                        data-status="<?php echo $row['status']; ?>"
                                        data-bs-toggle="modal" data-bs-target="#editStudentModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="student_history.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-dark btn-sm" title="View Ledger"><i class="fas fa-file-invoice"></i></a>
                                    <a href="fees.php?student_id=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm" title="Collect Fees"><i class="fas fa-money-bill-wave"></i></a>
                                    <a href="attendance.php?student_id=<?php echo $row['id']; ?>" class="btn btn-outline-info btn-sm" title="Attendance"><i class="fas fa-calendar-check"></i></a>
                                    <button class="btn btn-outline-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['student_name']); ?>')" title="Delete Student">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
      <form method="POST">
          <input type="hidden" name="action" value="add">
          <div class="modal-header border-0 bg-primary text-white" style="border-radius: 20px 20px 0 0;">
            <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Register New Student</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Student Name</label>
                    <input type="text" name="student_name" class="form-control" placeholder="Full Name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Father's Name</label>
                    <input type="text" name="father_name" class="form-control" placeholder="Father's Full Name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Number</label>
                    <input type="text" name="contact" class="form-control" placeholder="Mobile Number" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Email (Optional)">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">College/School Name</label>
                    <input type="text" name="college" class="form-control" placeholder="Current Institution" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold d-flex justify-content-between">
                        Course
                        <a href="courses.php" class="text-primary small text-decoration-none"><i class="fas fa-plus-circle me-1"></i>Add New</a>
                    </label>
                    <select name="course_id" class="form-select" required>
                        <option value="">Select Course</option>
                        <?php foreach($course_list as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Course Duration</label>
                    <select name="duration" class="form-select" required>
                        <option value="30_days">30 Days</option>
                        <option value="45_days">45 Days</option>
                        <option value="1_year">1 Year</option>
                    </select>
                </div>
            </div>
          </div>
          <div class="modal-footer border-0 p-4 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4">Register Student</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
      <form method="POST">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="student_id" id="edit_id">
          <div class="modal-header border-0 bg-warning text-dark" style="border-radius: 20px 20px 0 0;">
            <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Update Student Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Student Name</label>
                    <input type="text" name="student_name" id="edit_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Father's Name</label>
                    <input type="text" name="father_name" id="edit_father" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Number</label>
                    <input type="text" name="contact" id="edit_contact" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="email" id="edit_email" class="form-control">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">College/School Name</label>
                    <input type="text" name="college" id="edit_college" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Course</label>
                    <select name="course_id" id="edit_course" class="form-select" required>
                        <?php foreach($course_list as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Duration</label>
                    <select name="duration" id="edit_duration" class="form-select" required>
                        <option value="30_days">30 Days</option>
                        <option value="45_days">45 Days</option>
                        <option value="1_year">1 Year</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" id="edit_status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
          </div>
          <div class="modal-footer border-0 p-4 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning rounded-pill px-4 text-dark fw-bold">Save Changes</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_name').value = this.getAttribute('data-name');
            document.getElementById('edit_father').value = this.getAttribute('data-father');
            document.getElementById('edit_contact').value = this.getAttribute('data-contact');
            document.getElementById('edit_email').value = this.getAttribute('data-email');
            document.getElementById('edit_college').value = this.getAttribute('data-college');
            document.getElementById('edit_course').value = this.getAttribute('data-course');
            document.getElementById('edit_duration').value = this.getAttribute('data-duration');
            document.getElementById('edit_status').value = this.getAttribute('data-status');
        });
    });
});

function confirmDelete(id, name) {
    if (confirm("Are you sure you want to delete student '" + name + "'? This will also remove all their fee and attendance records.")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="student_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>

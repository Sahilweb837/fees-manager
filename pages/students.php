<?php
require_once '../includes/auth.php';
include '../includes/header.php';

$message = "";

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = $_POST['student_name'];
    $contact = $_POST['contact'];
    $course = $_POST['course'];
    
    $stmt = $conn->prepare("INSERT INTO students (student_name, contact, course, added_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $contact, $course, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $sid = $conn->insert_id;
        logActivity($conn, $_SESSION['user_id'], "Add Student", "Added student: $name (ID: $sid).");
        $message = "<div class='alert alert-success'>Student added successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error adding student.</div>";
    }
}

// Handle Edit Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $sid = $_POST['student_id'];
    $name = $_POST['student_name'];
    $contact = $_POST['contact'];
    $course = $_POST['course'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE students SET student_name=?, contact=?, course=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $contact, $course, $status, $sid);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], "Edit Student", "Updated student: $name (ID: $sid).");
        $message = "<div class='alert alert-success'>Student updated successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating student.</div>";
    }
}

// Fetch Students
$students = $conn->query("
    SELECT s.*, u.username as added_by_name 
    FROM students s 
    LEFT JOIN users u ON s.added_by = u.id 
    ORDER BY s.id DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Manage Students</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal"><i class="fas fa-plus"></i> Add New Student</button>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable w-100">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Contact</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Added By</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                        <td>
                            <?php if($row['status'] == 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['added_by_name']); ?></span></td>
                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-btn" 
                                data-id="<?php echo $row['id']; ?>"
                                data-name="<?php echo htmlspecialchars($row['student_name']); ?>"
                                data-contact="<?php echo htmlspecialchars($row['contact']); ?>"
                                data-course="<?php echo htmlspecialchars($row['course']); ?>"
                                data-status="<?php echo $row['status']; ?>"
                                data-bs-toggle="modal" data-bs-target="#editStudentModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="fees.php?student_id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm text-white" title="Collect Fees"><i class="fas fa-money-bill"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="">
          <input type="hidden" name="action" value="add">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="fas fa-user-plus"></i> Add New Student</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label>Student Name</label>
                <input type="text" name="student_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Contact Number</label>
                <input type="text" name="contact" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Course</label>
                <input type="text" name="course" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Student</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="student_id" id="edit_id">
          <div class="modal-header bg-warning">
            <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Student</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label>Student Name</label>
                <input type="text" name="student_name" id="edit_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Contact Number</label>
                <input type="text" name="contact" id="edit_contact" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Course</label>
                <input type="text" name="course" id="edit_course" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select name="status" id="edit_status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning">Update Student</button>
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
            document.getElementById('edit_contact').value = this.getAttribute('data-contact');
            document.getElementById('edit_course').value = this.getAttribute('data-course');
            document.getElementById('edit_status').value = this.getAttribute('data-status');
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../includes/auth.php';
include '../includes/header.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$course_filter = isset($_GET['course_id']) ? $_GET['course_id'] : '';

// Fetch Courses for Filter
$courses = $conn->query("SELECT * FROM courses ORDER BY course_name ASC");

// Fetch Attendance Stats for the selected date
$stats_query = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent
    FROM attendance 
    WHERE attendance_date = '$date'
");
$stats = $stats_query->fetch_assoc();

// Fetch Students with Attendance Status
$query = "
    SELECT s.id, s.student_name, s.father_name, c.course_name, 
           a.status as attendance_status, a.attendance_time
    FROM students s
    LEFT JOIN courses c ON s.course_id = c.id
    LEFT JOIN attendance a ON s.id = a.student_id AND a.attendance_date = '$date'
    WHERE s.status = 'active'
";
if ($course_filter) {
    $query .= " AND s.course_id = $course_filter";
}
$query .= " ORDER BY s.student_name ASC";
$students = $conn->query($query);
?>

<div class="animate-up">
    <!-- Header & Summary -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h2 class="fw-bold text-dark">Attendance</h2>
            <p class="text-muted">Register for <span class="text-primary fw-bold"><?php echo date('d M Y', strtotime($date)); ?></span></p>
        </div>
        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 glass-card bg-success-subtle border-success border-opacity-25 text-center">
                        <div class="small text-muted text-uppercase fw-bold">Present</div>
                        <div class="h3 fw-bold text-success mb-0"><?php echo $stats['present'] ?? 0; ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 glass-card bg-danger-subtle border-danger border-opacity-25 text-center">
                        <div class="small text-muted text-uppercase fw-bold">Absent (Fine ₹50)</div>
                        <div class="h3 fw-bold text-danger mb-0"><?php echo $stats['absent'] ?? 0; ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 glass-card bg-info-subtle border-info border-opacity-25 text-center">
                        <div class="small text-muted text-uppercase fw-bold">Total Fine</div>
                        <div class="h3 fw-bold text-info mb-0">₹<?php echo ($stats['absent'] ?? 0) * 50; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card glass-card border-0 mb-4 shadow-sm">
        <div class="card-body p-3">
            <form class="row g-3 align-items-center" id="filterForm">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar text-muted"></i></span>
                        <input type="date" name="date" class="form-control border-start-0" value="<?php echo $date; ?>" onchange="this.form.submit()">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-book text-muted"></i></span>
                        <select name="course_id" class="form-select border-start-0" onchange="this.form.submit()">
                            <option value="">All Courses</option>
                            <?php while($c = $courses->fetch_assoc()): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $course_filter == $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['course_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="attendanceSearch" class="form-control ps-5 rounded-pill" placeholder="Search by name...">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="attendance-msg" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    <!-- Attendance Table -->
    <div class="card glass-card border-0 shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="attendanceTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Student Profile</th>
                            <th>Course</th>
                            <th>Check-in Time</th>
                            <th>Daily Fine</th>
                            <th class="text-center pe-4">Mark Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $students->fetch_assoc()): ?>
                        <tr class="student-row">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs bg-primary-subtle text-primary rounded-circle me-3 d-flex align-items-center justify-content-center">
                                        <?php echo strtoupper(substr($row['student_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                        <a href="student_history.php?id=<?php echo $row['id']; ?>" class="small text-primary text-decoration-none">View History <i class="fas fa-external-link-alt" style="font-size: 10px;"></i></a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['course_name'] ?? 'N/A'); ?></span>
                            </td>
                            <td class="attendance-time-<?php echo $row['id']; ?>">
                                <?php if($row['attendance_status'] == 'present'): ?>
                                    <span class="text-success small fw-bold"><i class="far fa-clock me-1"></i><?php echo date('h:i A', strtotime($row['attendance_time'])); ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">--:--</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['attendance_status'] == 'absent'): ?>
                                    <span class="text-danger fw-bold">₹50.00</span>
                                <?php else: ?>
                                    <span class="text-muted">₹0.00</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group rounded-pill overflow-hidden border shadow-sm attendance-group" data-student-id="<?php echo $row['id']; ?>">
                                    <button type="button" 
                                        class="btn btn-sm px-3 mark-btn <?php echo $row['attendance_status'] == 'present' ? 'btn-success' : 'btn-white'; ?>" 
                                        onclick="markAttendance(<?php echo $row['id']; ?>, 'present')">
                                        P
                                    </button>
                                    <button type="button" 
                                        class="btn btn-sm px-3 mark-btn <?php echo $row['attendance_status'] == 'absent' ? 'btn-danger' : 'btn-white'; ?>" 
                                        onclick="markAttendance(<?php echo $row['id']; ?>, 'absent')">
                                        A
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

<style>
    .btn-white { background: #fff; color: #666; }
    .btn-white:hover { background: #f8f9fa; }
    .student-row:hover { background-color: rgba(255, 140, 0, 0.02) !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Custom Search
    const searchInput = document.getElementById('attendanceSearch');
    const table = document.getElementById('attendanceTable');
    const rows = table.getElementsByClassName('student-row');

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        for (let i = 0; i < rows.length; i++) {
            const name = rows[i].getElementsByClassName('fw-bold')[0].innerText.toLowerCase();
            if (name.indexOf(filter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    });
});

function markAttendance(studentId, status) {
    const date = "<?php echo $date; ?>";
    const time = new Date().toLocaleTimeString('en-GB', { hour12: false });
    
    fetch('save_attendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `student_id=${studentId}&status=${status}&date=${date}&time=${time}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Update UI Buttons
            const group = document.querySelector(`.attendance-group[data-student-id="${studentId}"]`);
            const btns = group.querySelectorAll('.mark-btn');
            
            if(status === 'present') {
                btns[0].className = 'btn btn-sm px-3 mark-btn btn-success';
                btns[1].className = 'btn btn-sm px-3 mark-btn btn-white';
            } else {
                btns[0].className = 'btn btn-sm px-3 mark-btn btn-white';
                btns[1].className = 'btn btn-sm px-3 mark-btn btn-danger';
            }
            
            // Update Time
            const timeCell = document.querySelector(`.attendance-time-${studentId}`);
            if(status === 'present') {
                timeCell.innerHTML = `<span class="text-success small fw-bold"><i class="far fa-clock me-1"></i>${data.formatted_time}</span>`;
            } else {
                timeCell.innerHTML = `<span class="text-muted small">--:--</span>`;
            }

            // Reload counts (simple way is refresh or separate AJAX)
            // For now, show toast
            showToast('Attendance recorded for ' + date, 'success');
        } else {
            showToast('Error: ' + data.message, 'danger');
        }
    });
}

function showToast(msg, type) {
    const container = document.getElementById('attendance-msg');
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} shadow-lg border-0 rounded-4 animate-up mb-2`;
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i> ${msg}`;
    container.appendChild(toast);
    setTimeout(() => { toast.remove(); }, 3000);
}
</script>

<?php include '../includes/footer.php'; ?>

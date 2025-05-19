<?php
require_once 'config.php';
requireRole('instructor');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

// Get course details
$course = getCourseById($course_id);
if (!$course || $course['instructor_id'] != $_SESSION['user_id']) {
    header("Location: instructor_dashboard.php");
    exit();
}

// Get student details
$student = getUserById($student_id);
if (!$student || $student['role'] != 'student') {
    header("Location: attendance_report.php?course_id=$course_id");
    exit();
}

// Verify student is enrolled
if (!isEnrolled($student_id, $course_id)) {
    header("Location: attendance_report.php?course_id=$course_id");
    exit();
}

// Get attendance data
$attendance_result = getAttendanceResult($student_id, $course_id);
$attendance_records = getStudentAttendance($student_id, $course_id);

$pageTitle = "Student Attendance";
require_once 'header.php';
?>

<div class="dashboard">
    <div class="sidebar">
        <h3 class="sidebar-title">Attendance</h3>
        <ul class="sidebar-menu">
            <li><a href="take_attendance.php?course_id=<?= $course_id ?>">
                <i class="fas fa-calendar-check"></i> Take Attendance
            </a></li>
            <li><a href="attendance_report.php?course_id=<?= $course_id ?>">
                <i class="fas fa-chart-bar"></i> Attendance Report
            </a></li>
            <li><a href="attendance_rules.php?course_id=<?= $course_id ?>">
                <i class="fas fa-cog"></i> Attendance Rules
            </a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h2>Attendance Details - <?= $student['full_name'] ?></h2>
            <a href="attendance_report.php?course_id=<?= $course_id ?>" class="btn btn-outline">
                Back to Report
            </a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Attendance Summary</h4>
                        <div class="card">
                            <div class="card-body">
                                <?php if ($attendance_result): ?>
                                    <div class="text-center">
                                        <h3><?= $attendance_result['attendance_percentage'] ?>%</h3>
                                        <p>Attendance Percentage</p>
                                        
                                        <div class="progress mb-3">
                                            <div class="progress-bar" 
                                                 style="width: <?= $attendance_result['attendance_percentage'] ?>%">
                                            </div>
                                        </div>
                                        
                                        <p>
                                            <strong><?= $attendance_result['attended_classes'] ?></strong> attended out of 
                                            <strong><?= $attendance_result['total_classes'] ?></strong> classes
                                        </p>
                                        
                                        <h4>Adjusted Grade: <?= $attendance_result['final_grade'] ?>%</h4>
                                    </div>
                                <?php else: ?>
                                    <p>No attendance data available yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h4>Update Attendance</h4>
                        <div class="card">
                            <div class="card-body">
                                <form method="post" action="update_attendance.php">
                                    <input type="hidden" name="course_id" value="<?= $course_id ?>">
                                    <input type="hidden" name="student_id" value="<?= $student_id ?>">
                                    
                                    <div class="form-group">
                                        <label for="date" class="form-label">Date</label>
                                        <input type="date" id="date" name="date" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="status" class="form-label">Status</label>
                                        <select id="status" name="status" class="form-control" required>
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="late">Late</option>
                                            <option value="excused">Excused</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Update Record</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4>Attendance History</h4>
                <div class="card">
                    <div class="card-body">
                        <?php if (!empty($attendance_records)): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Recorded By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendance_records as $record): 
                                        $recorded_by = getUserById($record['recorded_by']);
                                    ?>
                                        <tr>
                                            <td><?= date('M j, Y', strtotime($record['date'])) ?></td>
                                            <td>
                                                <span class="badge badge-<?= 
                                                    $record['status'] == 'present' ? 'success' : 
                                                    ($record['status'] == 'late' ? 'warning' : 'danger')
                                                ?>">
                                                    <?= ucfirst($record['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= $recorded_by['full_name'] ?></td>
                                            <td>
                                                <form method="post" action="delete_attendance.php" style="display: inline;">
                                                    <input type="hidden" name="attendance_id" value="<?= $record['attendance_id'] ?>">
                                                    <input type="hidden" name="course_id" value="<?= $course_id ?>">
                                                    <input type="hidden" name="student_id" value="<?= $student_id ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this record?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No attendance records found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
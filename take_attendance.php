<?php
require_once 'config.php';
requireRole('instructor');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get course details
$course = getCourseById($course_id);
if (!$course || $course['instructor_id'] != $_SESSION['user_id']) {
    header("Location: instructor_dashboard.php");
    exit();
}

// Get enrolled students
$students = $pdo->prepare("SELECT u.user_id, u.full_name, u.profile_picture, 
                          a.status as attendance_status
                          FROM enrollments e
                          JOIN users u ON e.student_id = u.user_id
                          LEFT JOIN attendance a ON a.student_id = u.user_id 
                              AND a.course_id = ? AND a.date = ?
                          WHERE e.course_id = ?
                          ORDER BY u.full_name");
$students->execute([$course_id, $date, $course_id]);
$students = $students->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['attendance'] as $student_id => $status) {
        recordAttendance($course_id, $student_id, $date, $status, $_SESSION['user_id']);
    }
    
    // Recalculate results for all students
    foreach ($students as $student) {
        calculateAttendanceResults($student['user_id'], $course_id);
    }
    
    $_SESSION['success_message'] = 'Attendance recorded successfully!';
    header("Location: take_attendance.php?course_id=$course_id&date=$date");
    exit();
}

$pageTitle = "Take Attendance";
require_once 'header.php';
?>

<div class="dashboard">
    <div class="sidebar">
        <h3 class="sidebar-title">Attendance</h3>
        <ul class="sidebar-menu">
            <li><a href="take_attendance.php?course_id=<?= $course_id ?>" class="active">
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
            <h2>Take Attendance - <?= $course['title'] ?></h2>
            <div>
                <form method="get" class="d-flex">
                    <input type="hidden" name="course_id" value="<?= $course_id ?>">
                    <input type="date" name="date" value="<?= $date ?>" class="form-control" style="margin-right: 10px;">
                    <button type="submit" class="btn btn-primary">Load</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form method="post">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Status</th>
                                <th>Recent Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): 
                                $recent = getStudentAttendance($student['user_id'], $course_id);
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= !empty($student['profile_picture']) ? 
                                                UPLOAD_DIR . 'profile/' . $student['profile_picture'] : 
                                                'assets/default.png' ?>" 
                                                alt="Profile" width="40" height="40" class="rounded-circle mr-3">
                                            <?= $student['full_name'] ?>
                                        </div>
                                    </td>
                                    <td>
                                        <select name="attendance[<?= $student['user_id'] ?>]" class="form-control">
                                            <option value="present" <?= ($student['attendance_status'] ?? '') == 'present' ? 'selected' : '' ?>>Present</option>
                                            <option value="absent" <?= ($student['attendance_status'] ?? '') == 'absent' ? 'selected' : '' ?>>Absent</option>
                                            <option value="late" <?= ($student['attendance_status'] ?? '') == 'late' ? 'selected' : '' ?>>Late</option>
                                            <option value="excused" <?= ($student['attendance_status'] ?? '') == 'excused' ? 'selected' : '' ?>>Excused</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <?php foreach (array_slice($recent, 0, 5) as $record): 
                                                $color = $record['status'] == 'present' ? 'success' : 
                                                         ($record['status'] == 'late' ? 'warning' : 'danger');
                                            ?>
                                                <span class="badge badge-<?= $color ?>" title="<?= date('M j', strtotime($record['date'])) ?>">
                                                    <?= substr($record['status'], 0, 1) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-primary">Save Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
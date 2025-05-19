<?php
require_once 'config.php';
requireRole(['instructor', 'student']);

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Get course details
$course = getCourseById($course_id);
if (!$course) {
    header("Location: ". (hasRole('instructor') ? 'instructor_dashboard.php' : 'student_dashboard.php'));
    exit();
}

// For instructors, verify they teach this course
if (hasRole('instructor') && $course['instructor_id'] != $_SESSION['user_id']) {
    header("Location: instructor_dashboard.php");
    exit();
}

// For students, verify they're enrolled
if (hasRole('student') && !isEnrolled($_SESSION['user_id'], $course_id)) {
    header("Location: student_dashboard.php");
    exit();
}

// Get attendance data
if (hasRole('instructor')) {
    // Instructor view - all students
    $students = $pdo->prepare("SELECT u.user_id, u.full_name, 
                              COUNT(a.attendance_id) as total_attendance,
                              SUM(a.status = 'present' OR a.status = 'late') as attended,
                              ar.attendance_percentage, ar.final_grade
                              FROM enrollments e
                              JOIN users u ON e.student_id = u.user_id
                              LEFT JOIN attendance a ON a.student_id = u.user_id AND a.course_id = ?
                              LEFT JOIN attendance_results ar ON ar.student_id = u.user_id AND ar.course_id = ?
                              WHERE e.course_id = ?
                              GROUP BY u.user_id
                              ORDER BY u.full_name");
    $students->execute([$course_id, $course_id, $course_id]);
    $students = $students->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Student view - only their data
    $attendance_result = getAttendanceResult($_SESSION['user_id'], $course_id);
    $attendance_records = getStudentAttendance($_SESSION['user_id'], $course_id);
}

$pageTitle = "Attendance Report";
require_once 'header.php';
?>

<div class="dashboard">
    <div class="sidebar">
        <h3 class="sidebar-title">Attendance</h3>
        <ul class="sidebar-menu">
            <?php if (hasRole('instructor')): ?>
                <li><a href="take_attendance.php?course_id=<?= $course_id ?>">
                    <i class="fas fa-calendar-check"></i> Take Attendance
                </a></li>
            <?php endif; ?>
            <li><a href="attendance_report.php?course_id=<?= $course_id ?>" class="active">
                <i class="fas fa-chart-bar"></i> Attendance Report
            </a></li>
            <?php if (hasRole('instructor')): ?>
                <li><a href="attendance_rules.php?course_id=<?= $course_id ?>">
                    <i class="fas fa-cog"></i> Attendance Rules
                </a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h2>Attendance Report - <?= $course['title'] ?></h2>
        </div>
        
        <?php if (hasRole('instructor')): ?>
            <!-- Instructor View -->
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Classes Attended</th>
                                <th>Attendance %</th>
                                <th>Adjusted Grade</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= $student['full_name'] ?></td>
                                    <td>
                                        <?= $student['attended'] ?? 0 ?> / <?= $student['total_attendance'] ?? 0 ?>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" 
                                                 style="width: <?= $student['attendance_percentage'] ?? 0 ?>%">
                                                <?= $student['attendance_percentage'] ?? 0 ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= $student['final_grade'] ?? 'N/A' ?>%
                                    </td>
                                    <td>
                                        <a href="student_attendance.php?course_id=<?= $course_id ?>&student_id=<?= $student['user_id'] ?>" 
                                           class="btn btn-sm btn-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <!-- Student View -->
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
                            <h4>Attendance History</h4>
                            <div class="card">
                                <div class="card-body">
                                    <?php if (!empty($attendance_records)): ?>
                                        <ul class="list-group">
                                            <?php foreach ($attendance_records as $record): 
                                                $badge_class = $record['status'] == 'present' ? 'success' : 
                                                              ($record['status'] == 'late' ? 'warning' : 'danger');
                                            ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <?= date('M j, Y', strtotime($record['date'])) ?>
                                                    <span class="badge badge-<?= $badge_class ?>">
                                                        <?= ucfirst($record['status']) ?>
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>No attendance records found.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
<?php
require_once 'config.php';
requireRole('instructor');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Get course details
$course = getCourseById($course_id);
if (!$course || $course['instructor_id'] != $_SESSION['user_id']) {
    header("Location: instructor_dashboard.php");
    exit();
}

// Get existing rules
$rules = getAttendanceRules($course_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $min_presence = intval($_POST['min_presence']);
    $grade_adjustment = intval($_POST['grade_adjustment']);
    $max_grade = intval($_POST['max_grade']);
    
    if (setAttendanceRules($course_id, $min_presence, $grade_adjustment, $max_grade)) {
        $_SESSION['success_message'] = 'Attendance rules updated successfully!';
        
        // Recalculate all results for this course
        $students = $pdo->prepare("SELECT student_id FROM enrollments WHERE course_id = ?");
        $students->execute([$course_id]);
        while ($student = $students->fetch(PDO::FETCH_ASSOC)) {
            calculateAttendanceResults($student['student_id'], $course_id);
        }
        
        header("Location: attendance_rules.php?course_id=$course_id");
        exit();
    } else {
        $error = 'Failed to update attendance rules. Please try again.';
    }
}

$pageTitle = "Attendance Rules";
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
            <li><a href="attendance_rules.php?course_id=<?= $course_id ?>" class="active">
                <i class="fas fa-cog"></i> Attendance Rules
            </a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h2>Attendance Rules - <?= $course['title'] ?></h2>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="post">
                    <div class="form-group">
                        <label for="min_presence" class="form-label">Minimum Presence Percentage</label>
                        <div class="input-group">
                            <input type="number" id="min_presence" name="min_presence" 
                                   class="form-control" min="0" max="100" 
                                   value="<?= $rules['min_presence_percentage'] ?? 75 ?>" required>
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="text-muted">Students below this percentage will receive grade adjustment</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="grade_adjustment" class="form-label">Grade Adjustment</label>
                        <div class="input-group">
                            <input type="number" id="grade_adjustment" name="grade_adjustment" 
                                   class="form-control" min="0" max="100" 
                                   value="<?= $rules['grade_adjustment'] ?? 0 ?>" required>
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="text-muted">Percentage points to deduct from final grade for low attendance</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="max_grade" class="form-label">Maximum Possible Grade</label>
                        <div class="input-group">
                            <input type="number" id="max_grade" name="max_grade" 
                                   class="form-control" min="0" max="100" 
                                   value="<?= $rules['max_grade_with_attendance'] ?? 100 ?>" required>
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="text-muted">Maximum grade achievable considering attendance</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Save Rules</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
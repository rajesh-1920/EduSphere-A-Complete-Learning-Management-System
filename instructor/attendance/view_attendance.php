<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

$db = new Database();

// Get instructor's courses for dropdown
$courses = $db->query("
    SELECT course_id, title 
    FROM courses 
    WHERE instructor_id = $instructorId
    ORDER BY title
")->fetch_all(MYSQLI_ASSOC);

// Get students for selected course
$students = [];
if ($courseId > 0) {
    $students = $db->query("
        SELECT u.user_id, u.first_name, u.last_name
        FROM enrollments e
        JOIN users u ON e.student_id = u.user_id
        WHERE e.course_id = $courseId AND e.status = 'active'
        ORDER BY u.last_name, u.first_name
    ")->fetch_all(MYSQLI_ASSOC);
}

// Get attendance records based on filters
$attendanceRecords = [];
if ($courseId > 0) {
    $query = "
        SELECT a.*, u.first_name, u.last_name, c.title as course_title
        FROM attendance a
        JOIN users u ON a.student_id = u.user_id
        JOIN courses c ON a.course_id = c.course_id
        WHERE a.course_id = $courseId
    ";
    
    if ($studentId > 0) {
        $query .= " AND a.student_id = $studentId";
    }
    
    $query .= " ORDER BY a.date DESC, u.last_name, u.first_name";
    
    $attendanceRecords = $db->query($query)->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = 'View Attendance';
require_once '../../includes/header.php';
?>

<div class="attendance-container">
    <h1>View Attendance Records</h1>
    
    <form method="get" class="filter-form">
        <div class="form-group">
            <label for="course_id">Select Course</label>
            <select id="course_id" name="course_id" required>
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['course_id']; ?>" <?php echo $course['course_id'] == $courseId ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <?php if ($courseId > 0 && !empty($students)): ?>
        <div class="form-group">
            <label for="student_id">Select Student (Optional)</label>
            <select id="student_id" name="student_id">
                <option value="">-- All Students --</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo $student['user_id']; ?>" <?php echo $student['user_id'] == $studentId ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <button type="submit" class="btn">Filter</button>
    </form>
    
    <?php if ($courseId > 0 && !empty($attendanceRecords)): ?>
        <div class="attendance-summary">
            <h2><?php echo htmlspecialchars($attendanceRecords[0]['course_title']); ?></h2>
            <?php if ($studentId > 0): ?>
                <h3><?php echo htmlspecialchars($attendanceRecords[0]['first_name'] . ' ' . $attendanceRecords[0]['last_name']); ?></h3>
            <?php endif; ?>
        </div>
        
        <table class="attendance-table">
            <thead>
                <tr>
                    <?php if ($studentId == 0): ?>
                        <th>Student</th>
                    <?php endif; ?>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendanceRecords as $record): ?>
                    <tr>
                        <?php if ($studentId == 0): ?>
                            <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                        <?php endif; ?>
                        <td><?php echo date('M j, Y', strtotime($record['date'])); ?></td>
                        <td>
                            <span class="status-badge <?php echo $record['status']; ?>">
                                <?php echo ucfirst($record['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($record['notes']); ?></td>
                        <td>
                            <?php 
                                $recordedBy = getUserById($record['recorded_by']);
                                echo htmlspecialchars($recordedBy['first_name'] . ' ' . $recordedBy['last_name']);
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($courseId > 0): ?>
        <p>No attendance records found for the selected filters.</p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
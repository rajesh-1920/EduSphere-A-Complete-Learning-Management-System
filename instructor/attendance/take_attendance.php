<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$db = new Database();

// Get instructor's courses for dropdown
$courses = $db->query("
    SELECT course_id, title 
    FROM courses 
    WHERE instructor_id = $instructorId
    ORDER BY title
")->fetch_all(MYSQLI_ASSOC);

// Get enrolled students if course is selected
$students = [];
if ($courseId > 0) {
    $students = $db->query("
        SELECT u.user_id, u.first_name, u.last_name, 
               (SELECT status FROM attendance 
                WHERE student_id = u.user_id 
                AND course_id = $courseId 
                AND date = '$date' LIMIT 1) as status
        FROM enrollments e
        JOIN users u ON e.student_id = u.user_id
        WHERE e.course_id = $courseId AND e.status = 'active'
        ORDER BY u.last_name, u.first_name
    ")->fetch_all(MYSQLI_ASSOC);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $date = $_POST['date'];
    $courseId = (int)$_POST['course_id'];
    
    foreach ($_POST['attendance'] as $studentId => $status) {
        // Check if attendance record already exists
        $exists = $db->query("
            SELECT attendance_id FROM attendance 
            WHERE student_id = $studentId 
            AND course_id = $courseId 
            AND date = '$date'
        ")->fetch_row();
        
        if ($exists) {
            // Update existing record
            $stmt = $db->prepare("
                UPDATE attendance 
                SET status = ?, notes = ?, recorded_by = ?, recorded_at = NOW()
                WHERE attendance_id = ?
            ");
            $stmt->bind_param("ssii", $status, $_POST['notes'][$studentId], $instructorId, $exists[0]);
        } else {
            // Insert new record
            $stmt = $db->prepare("
                INSERT INTO attendance 
                (course_id, student_id, date, status, notes, recorded_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iisssi", $courseId, $studentId, $date, $status, $_POST['notes'][$studentId], $instructorId);
        }
        $stmt->execute();
    }
    
    $_SESSION['success_message'] = 'Attendance recorded successfully!';
    redirect("take_attendance.php?course_id=$courseId&date=$date");
}

$pageTitle = 'Take Attendance';
require_once '../../includes/header.php';
?>

<div class="attendance-container">
    <h1>Take Attendance</h1>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    
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
        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required>
        </div>
        <button type="submit" class="btn">Load Students</button>
    </form>
    
    <?php if ($courseId > 0 && !empty($students)): ?>
        <form method="post">
            <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
            <input type="hidden" name="date" value="<?php echo $date; ?>">
            
            <div class="attendance-header">
                <h2><?php echo htmlspecialchars($courses[array_search($courseId, array_column($courses, 'course_id'))]['title']); ?></h2>
                <h3><?php echo date('F j, Y', strtotime($date)); ?></h3>
            </div>
            
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                            </td>
                            <td>
                                <select name="attendance[<?php echo $student['user_id']; ?>]" required>
                                    <option value="present" <?php echo $student['status'] === 'present' ? 'selected' : ''; ?>>Present</option>
                                    <option value="absent" <?php echo $student['status'] === 'absent' ? 'selected' : ''; ?>>Absent</option>
                                    <option value="late" <?php echo $student['status'] === 'late' ? 'selected' : ''; ?>>Late</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="notes[<?php echo $student['user_id']; ?>]" 
                                       value="<?php echo isset($student['notes']) ? htmlspecialchars($student['notes']) : ''; ?>" 
                                       placeholder="Optional notes">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Attendance</button>
            </div>
        </form>
    <?php elseif ($courseId > 0): ?>
        <p>No active students enrolled in this course.</p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
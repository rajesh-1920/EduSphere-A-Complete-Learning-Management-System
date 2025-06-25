<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

$db = new Database();

// Get instructor's courses for dropdown
$courses = $db->query("
    SELECT course_id, title 
    FROM courses 
    WHERE instructor_id = $instructorId
    ORDER BY title
")->fetch_all(MYSQLI_ASSOC);

// Get attendance summary if course is selected
$attendanceSummary = [];
$studentDetails = [];
if ($courseId > 0) {
    // Get list of students in the course
    $studentDetails = $db->query("
        SELECT u.user_id, u.first_name, u.last_name
        FROM enrollments e
        JOIN users u ON e.student_id = u.user_id
        WHERE e.course_id = $courseId AND e.status = 'active'
        ORDER BY u.last_name, u.first_name
    ")->fetch_all(MYSQLI_ASSOC);

    if (!empty($studentDetails)) {
        $studentIds = array_column($studentDetails, 'user_id');
        $studentIdsStr = implode(',', $studentIds);

        // Get attendance summary for each student
        $attendanceSummary = $db->query("
            SELECT 
                student_id,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                COUNT(*) as total
            FROM attendance
            WHERE course_id = $courseId 
            AND student_id IN ($studentIdsStr)
            AND date BETWEEN '$startDate' AND '$endDate'
            GROUP BY student_id
        ")->fetch_all(MYSQLI_ASSOC);

        // Convert to associative array by student_id
        $tempSummary = [];
        foreach ($attendanceSummary as $summary) {
            $tempSummary[$summary['student_id']] = $summary;
        }
        $attendanceSummary = $tempSummary;
    }
}

$pageTitle = 'Attendance Report';
require_once '../../includes/header.php';
?>

<div class="attendance-report">
    <h1>Attendance Report</h1>

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
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required>
        </div>
        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" required>
        </div>
        <button type="submit" class="btn">Generate Report</button>
    </form>

    <?php if ($courseId > 0 && !empty($studentDetails)): ?>
        <div class="report-header">
            <h2><?php echo htmlspecialchars($courses[array_search($courseId, array_column($courses, 'course_id'))]['title']); ?></h2>
            <h3>Attendance from <?php echo date('M j, Y', strtotime($startDate)); ?> to <?php echo date('M j, Y', strtotime($endDate)); ?></h3>
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Total Classes</th>
                    <th>Attendance Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($studentDetails as $student):
                    $summary = $attendanceSummary[$student['user_id']] ?? [
                        'present' => 0,
                        'absent' => 0,
                        'late' => 0,
                        'total' => 0
                    ];
                    $attendanceRate = $summary['total'] > 0 ? round(($summary['present'] + $summary['late'] * 0.5) / $summary['total'] * 100, 2) : 0;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td><?php echo $summary['present']; ?></td>
                        <td><?php echo $summary['absent']; ?></td>
                        <td><?php echo $summary['late']; ?></td>
                        <td><?php echo $summary['total']; ?></td>
                        <td>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?php echo $attendanceRate; ?>%"></div>
                                <span><?php echo $attendanceRate; ?>%</span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="report-actions">
            <button onclick="window.print()" class="btn">Print Report</button>
            <a href="export_attendance.php?course_id=<?php echo $courseId; ?>&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" class="btn">Export to CSV</a>
        </div>
    <?php elseif ($courseId > 0): ?>
        <p>No active students enrolled in this course.</p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
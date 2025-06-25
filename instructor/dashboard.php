<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$db = new Database();

// Get instructor's courses
$courses = $db->query("
    SELECT c.course_id, c.title, COUNT(e.enrollment_id) as students
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.status = 'active'
    WHERE c.instructor_id = $instructorId
    GROUP BY c.course_id
    ORDER BY c.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get total students
$totalStudents = $db->query("
    SELECT COUNT(DISTINCT e.student_id) 
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE c.instructor_id = $instructorId AND e.status = 'active'
")->fetch_row()[0];

// Get recent announcements
$announcements = $db->query("
    SELECT a.announcement_id, a.title, a.created_at, c.title as course_title
    FROM announcements a
    JOIN courses c ON a.course_id = c.course_id
    WHERE c.instructor_id = $instructorId
    ORDER BY a.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get pending assignments to grade
$pendingAssignments = $db->query("
    SELECT a.assignment_id, a.title, c.title as course_title, COUNT(s.submission_id) as submissions
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    LEFT JOIN assignment_submissions s ON a.assignment_id = s.assignment_id AND s.grade IS NULL
    WHERE c.instructor_id = $instructorId
    GROUP BY a.assignment_id
    HAVING submissions > 0
    ORDER BY a.due_date ASC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get recent attendance records
$recentAttendance = $db->query("
    SELECT a.date, c.title as course_title, 
           COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
           COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
           COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late
    FROM attendance a
    JOIN courses c ON a.course_id = c.course_id
    WHERE c.instructor_id = $instructorId
    GROUP BY a.date, a.course_id
    ORDER BY a.date DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Instructor Dashboard';
require_once '../includes/header.php';
?>

<div class="instructor-dashboard">
    <h1>Welcome, <?php echo $_SESSION['first_name']; ?></h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Your Courses</h3>
            <p><?php echo count($courses); ?></p>
            <a href="courses/manage_courses.php">View All</a>
        </div>
        <div class="stat-card">
            <h3>Total Students</h3>
            <p><?php echo $totalStudents; ?></p>
        </div>
        <div class="stat-card">
            <h3>Attendance</h3>
            <p><?php echo !empty($recentAttendance) ? 'Recent records' : 'No records'; ?></p>
            <a href="attendance/take_attendance.php">Manage</a>
        </div>
    </div>

    <div class="dashboard-section">
        <h2>Your Courses</h2>
        <?php if (!empty($courses)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td><?php echo $course['students']; ?></td>
                            <td>
                                <a href="courses/view_course.php?id=<?php echo $course['course_id']; ?>" class="btn">View</a>
                                <a href="attendance/take_attendance.php?course_id=<?php echo $course['course_id']; ?>" class="btn">Attendance</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You don't have any courses yet. <a href="courses/add_course.php">Create your first course</a></p>
        <?php endif; ?>
    </div>

    <div class="dashboard-section">
        <h2>Recent Attendance</h2>
        <?php if (!empty($recentAttendance)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Course</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentAttendance as $record): ?>
                        <tr>
                            <td><?php echo formatDate($record['date']); ?></td>
                            <td><?php echo htmlspecialchars($record['course_title']); ?></td>
                            <td><?php echo $record['present']; ?></td>
                            <td><?php echo $record['absent']; ?></td>
                            <td><?php echo $record['late']; ?></td>
                            <td>
                                <a href="attendance/view_attendance.php?date=<?php echo $record['date']; ?>&course_id=<?php echo array_search($record['course_title'], array_column($courses, 'title')) ? $courses[array_search($record['course_title'], array_column($courses, 'title'))]['course_id'] : ''; ?>" class="btn">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="view-all">
                <a href="attendance/view_attendance.php" class="btn">View All Attendance</a>
            </div>
        <?php else: ?>
            <p>No attendance records yet. <a href="attendance/take_attendance.php">Take attendance</a></p>
        <?php endif; ?>
    </div>

    <div class="dashboard-section">
        <h2>Recent Announcements</h2>
        <?php if (!empty($announcements)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Course</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($announcements as $announcement): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                            <td><?php echo htmlspecialchars($announcement['course_title']); ?></td>
                            <td><?php echo formatDate($announcement['created_at']); ?></td>
                            <td>
                                <a href="announcements/edit_announcement.php?id=<?php echo $announcement['announcement_id']; ?>" class="btn">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No announcements yet. <a href="announcements/add_announcement.php">Create your first announcement</a></p>
        <?php endif; ?>
    </div>

    <div class="dashboard-section">
        <h2>Assignments to Grade</h2>
        <?php if (!empty($pendingAssignments)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Assignment</th>
                        <th>Course</th>
                        <th>Submissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingAssignments as $assignment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['course_title']); ?></td>
                            <td><?php echo $assignment['submissions']; ?></td>
                            <td>
                                <a href="assignments/submissions/view_submissions.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn">Grade</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No assignments to grade at this time.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
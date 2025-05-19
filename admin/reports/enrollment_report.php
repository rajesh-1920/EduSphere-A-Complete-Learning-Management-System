<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['admin']);

$db = new Database();

// Get enrollment statistics
$totalEnrollments = $db->query("SELECT COUNT(*) FROM enrollments")->fetch_row()[0];
$activeEnrollments = $db->query("SELECT COUNT(*) FROM enrollments WHERE status = 'active'")->fetch_row()[0];
$completedEnrollments = $db->query("SELECT COUNT(*) FROM enrollments WHERE status = 'completed'")->fetch_row()[0];

// Get enrollments by course
$enrollmentsByCourse = $db->query("
    SELECT c.course_id, c.title, COUNT(e.enrollment_id) as enrollment_count
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    GROUP BY c.course_id
    ORDER BY enrollment_count DESC
")->fetch_all(MYSQLI_ASSOC);

// Get recent enrollments
$recentEnrollments = $db->query("
    SELECT e.enrolled_at, u.first_name, u.last_name, c.title
    FROM enrollments e
    JOIN users u ON e.student_id = u.user_id
    JOIN courses c ON e.course_id = c.course_id
    ORDER BY e.enrolled_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Enrollment Report';
require_once '../../includes/header.php';
?>

<div class="enrollment-report">
    <h1>Enrollment Report</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Enrollments</h3>
            <p><?php echo $totalEnrollments; ?></p>
        </div>
        <div class="stat-card">
            <h3>Active Enrollments</h3>
            <p><?php echo $activeEnrollments; ?></p>
        </div>
        <div class="stat-card">
            <h3>Completed Enrollments</h3>
            <p><?php echo $completedEnrollments; ?></p>
        </div>
    </div>
    
    <div class="report-section">
        <h2>Enrollments by Course</h2>
        <table>
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Enrollments</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enrollmentsByCourse as $course): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['title']); ?></td>
                        <td><?php echo $course['enrollment_count']; ?></td>
                        <td>
                            <a href="../courses/view_course.php?id=<?php echo $course['course_id']; ?>" class="btn">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="report-section">
        <h2>Recent Enrollments</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Course</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentEnrollments as $enrollment): ?>
                    <tr>
                        <td><?php echo formatDate($enrollment['enrolled_at']); ?></td>
                        <td><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($enrollment['title']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

$studentId = $_SESSION['user_id'];
$db = new Database();

// Get enrolled courses
$courses = $db->query("
    SELECT c.course_id, c.title, c.description, c.thumbnail, u.first_name, u.last_name
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    JOIN users u ON c.instructor_id = u.user_id
    WHERE e.student_id = $studentId AND e.status = 'active'
    ORDER BY e.enrolled_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get upcoming assignments
$assignments = $db->query("
    SELECT a.assignment_id, a.title, a.due_date, c.title as course_title
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON a.course_id = e.course_id
    WHERE e.student_id = $studentId AND e.status = 'active' AND a.due_date > NOW()
    ORDER BY a.due_date ASC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get recent announcements
$announcements = $db->query("
    SELECT a.announcement_id, a.title, a.content, a.created_at, c.title as course_title
    FROM announcements a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON a.course_id = e.course_id
    WHERE e.student_id = $studentId AND e.status = 'active'
    ORDER BY a.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Student Dashboard';
require_once '../../includes/header.php';
?>

<div class="student-dashboard">
    <h1>Welcome, <?php echo $_SESSION['first_name']; ?></h1>
    
    <div class="dashboard-section">
        <h2>Your Courses</h2>
        <?php if (!empty($courses)): ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <?php if ($course['thumbnail']): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/course_thumbnails/<?php echo $course['thumbnail']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <?php endif; ?>
                        <div class="course-info">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="instructor">Instructor: <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></p>
                            <p class="description"><?php echo htmlspecialchars($course['description']); ?></p>
                            <a href="courses/view_course.php?id=<?php echo $course['course_id']; ?>" class="btn">View Course</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all">
                <a href="courses/my_courses.php" class="btn">View All Courses</a>
            </div>
        <?php else: ?>
            <p>You are not enrolled in any courses yet. <a href="courses/enroll.php">Browse available courses</a></p>
        <?php endif; ?>
    </div>
    
    <div class="dashboard-columns">
        <div class="column">
            <h2>Upcoming Assignments</h2>
            <?php if (!empty($assignments)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Assignment</th>
                            <th>Course</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['course_title']); ?></td>
                                <td><?php echo formatDate($assignment['due_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="view-all">
                    <a href="assignments/my_assignments.php" class="btn">View All Assignments</a>
                </div>
            <?php else: ?>
                <p>No upcoming assignments.</p>
            <?php endif; ?>
        </div>
        
        <div class="column">
            <h2>Recent Announcements</h2>
            <?php if (!empty($announcements)): ?>
                <div class="announcements-list">
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement-card">
                            <h4><?php echo htmlspecialchars($announcement['title']); ?></h4>
                            <p class="course"><?php echo htmlspecialchars($announcement['course_title']); ?></p>
                            <p class="date"><?php echo formatDate($announcement['created_at']); ?></p>
                            <p class="content"><?php echo nl2br(htmlspecialchars(substr($announcement['content'], 0, 100))); ?>...</p>
                            <a href="announcements/view_announcement.php?id=<?php echo $announcement['announcement_id']; ?>" class="btn">Read More</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="view-all">
                    <a href="announcements/view_announcements.php" class="btn">View All Announcements</a>
                </div>
            <?php else: ?>
                <p>No recent announcements.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
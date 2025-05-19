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
    SELECT c.course_id, c.title, c.description, c.thumbnail, u.first_name, u.last_name, e.enrolled_at
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    JOIN users u ON c.instructor_id = u.user_id
    WHERE e.student_id = $studentId AND e.status = 'active'
    ORDER BY e.enrolled_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'My Courses';
require_once '../../includes/header.php';
?>

<div class="my-courses">
    <h1>My Courses</h1>
    
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
                        <p class="enrolled">Enrolled: <?php echo formatDate($course['enrolled_at']); ?></p>
                        <p class="description"><?php echo htmlspecialchars($course['description']); ?></p>
                        <a href="view_course.php?id=<?php echo $course['course_id']; ?>" class="btn">View Course</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>You are not enrolled in any courses yet. <a href="enroll.php">Browse available courses</a></p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
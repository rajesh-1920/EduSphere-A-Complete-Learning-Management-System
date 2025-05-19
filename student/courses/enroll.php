<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

$studentId = $_SESSION['user_id'];
$db = new Database();

// Handle enrollment
if (isset($_GET['enroll']) && is_numeric($_GET['enroll'])) {
    $courseId = $_GET['enroll'];
    
    // Check if already enrolled
    $check = $db->query("
        SELECT enrollment_id FROM enrollments 
        WHERE student_id = $studentId AND course_id = $courseId
    ")->fetch_row();
    
    if (!$check) {
        $stmt = $db->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $studentId, $courseId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Successfully enrolled in the course!';
            redirect('my_courses.php');
        } else {
            $_SESSION['error_message'] = 'Failed to enroll in the course. Please try again.';
        }
    } else {
        $_SESSION['error_message'] = 'You are already enrolled in this course.';
    }
}

// Get available courses (not already enrolled)
$availableCourses = $db->query("
    SELECT c.*, u.first_name, u.last_name
    FROM courses c
    JOIN users u ON c.instructor_id = u.user_id
    WHERE c.course_id NOT IN (
        SELECT course_id FROM enrollments WHERE student_id = $studentId
    )
    ORDER BY c.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Enroll in Courses';
require_once '../../includes/header.php';
?>

<div class="enroll-courses">
    <h1>Available Courses</h1>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($availableCourses)): ?>
        <div class="courses-grid">
            <?php foreach ($availableCourses as $course): ?>
                <div class="course-card">
                    <?php if ($course['thumbnail']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/course_thumbnails/<?php echo $course['thumbnail']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                    <?php endif; ?>
                    <div class="course-info">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="instructor">Instructor: <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></p>
                        <p class="description"><?php echo htmlspecialchars($course['description']); ?></p>
                        <a href="enroll.php?enroll=<?php echo $course['course_id']; ?>" class="btn">Enroll Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No available courses to enroll in at this time.</p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
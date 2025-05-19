<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Redirect to appropriate dashboard if logged in
if (isLoggedIn()) {
    redirect($_SESSION['role'] . '/dashboard.php');
}

// Get featured courses
$db = new Database();
$featuredCourses = $db->query("
    SELECT c.*, u.first_name, u.last_name
    FROM courses c
    JOIN users u ON c.instructor_id = u.user_id
    ORDER BY c.created_at DESC
    LIMIT 3
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Welcome to ' . SITE_NAME;
require_once 'includes/header.php';
?>

<div class="hero">
    <div class="container">
        <h1>Welcome to <?php echo SITE_NAME; ?></h1>
        <p>Your gateway to online learning and professional development</p>
        <div class="hero-actions">
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary">Get Started</a>
                <a href="login.php" class="btn btn-secondary">Login</a>
            <?php else: ?>
                <a href="<?php echo $_SESSION['role']; ?>/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="features">
    <div class="container">
        <h2>Why Choose <?php echo SITE_NAME; ?>?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-laptop"></i>
                <h3>Learn Anywhere</h3>
                <p>Access your courses from any device, anytime, anywhere.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>Expert Instructors</h3>
                <p>Learn from industry professionals with real-world experience.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-certificate"></i>
                <h3>Certification</h3>
                <p>Earn certificates to showcase your new skills.</p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($featuredCourses)): ?>
<div class="featured-courses">
    <div class="container">
        <h2>Featured Courses</h2>
        <div class="courses-grid">
            <?php foreach ($featuredCourses as $course): ?>
                <div class="course-card">
                    <?php if ($course['thumbnail']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/course_thumbnails/<?php echo $course['thumbnail']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                    <?php endif; ?>
                    <div class="course-info">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="instructor">Instructor: <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></p>
                        <p class="description"><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...</p>
                        <?php if (!isLoggedIn()): ?>
                            <a href="register.php" class="btn">Enroll Now</a>
                        <?php else: ?>
                            <a href="courses/enroll.php?enroll=<?php echo $course['course_id']; ?>" class="btn">Enroll Now</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!isLoggedIn()): ?>
            <div class="view-all">
                <a href="register.php" class="btn">View All Courses</a>
            </div>
        <?php else: ?>
            <div class="view-all">
                <a href="courses/enroll.php" class="btn">View All Courses</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="testimonials">
    <div class="container">
        <h2>What Our Students Say</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="quote">"This platform has transformed my learning experience. The courses are well-structured and the instructors are knowledgeable."</div>
                <div class="author">- Sarah Johnson</div>
            </div>
            <div class="testimonial-card">
                <div class="quote">"I was able to advance my career thanks to the skills I gained from these courses. Highly recommended!"</div>
                <div class="author">- Michael Chen</div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
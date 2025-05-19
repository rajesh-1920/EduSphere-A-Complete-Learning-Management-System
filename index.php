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
    SELECT c.*, u.first_name, u.last_name, COUNT(e.enrollment_id) as students
    FROM courses c
    JOIN users u ON c.instructor_id = u.user_id
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    GROUP BY c.course_id
    ORDER BY c.created_at DESC
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Get statistics
$totalCourses = $db->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];
$totalStudents = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0];
$totalInstructors = $db->query("SELECT COUNT(*) FROM users WHERE role = 'instructor'")->fetch_row()[0];

$pageTitle = 'Welcome to ' . SITE_NAME;
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Unlock Your Potential With <span>EduSphere</span></h1>
            <p class="hero-subtitle">Learn from industry experts and advance your career with our comprehensive online courses</p>
            <div class="hero-actions">
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-primary">Get Started for Free</a>
                    <a href="login.php" class="btn btn-outline">Login</a>
                <?php else: ?>
                    <a href="<?php echo $_SESSION['role']; ?>/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-image">
            <img src="<?php echo SITE_URL; ?>/assets/images/hero-image.png" alt="Online Learning">
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number" data-count="<?php echo $totalCourses; ?>">0</div>
                <div class="stat-label">Courses</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-count="<?php echo $totalStudents; ?>">0</div>
                <div class="stat-label">Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-count="<?php echo $totalInstructors; ?>">0</div>
                <div class="stat-label">Instructors</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <h2 class="section-title">Why Choose EduSphere?</h2>
        <p class="section-subtitle">We provide the best learning experience for our students</p>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h3>Interactive Learning</h3>
                <p>Engage with interactive content, quizzes, and hands-on projects to enhance your learning experience.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h3>Expert Instructors</h3>
                <p>Learn from industry professionals with years of practical experience in their fields.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3>Certification</h3>
                <p>Earn recognized certificates upon course completion to showcase your new skills.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Lifetime Access</h3>
                <p>Get lifetime access to course materials so you can learn at your own pace.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Friendly</h3>
                <p>Access your courses anytime, anywhere on any device with our responsive platform.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>24/7 Support</h3>
                <p>Our dedicated support team is always ready to help you with any questions.</p>
            </div>
        </div>
    </div>
</section>

<!-- Courses Section -->
<section class="courses">
    <div class="container">
        <h2 class="section-title">Featured Courses</h2>
        <p class="section-subtitle">Browse our most popular courses</p>

        <div class="courses-slider">
            <?php foreach ($featuredCourses as $course): ?>
                <div class="course-card">
                    <div class="course-content">
                        <div class="course-meta">
                            <span class="course-students"><i class="fas fa-users"></i> <?php echo $course['students']; ?> students</span>
                            <span class="course-instructor"><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></span>
                        </div>
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="course-description"><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...</p>
                        <div class="course-footer">
                            <?php if (!isLoggedIn()): ?>
                                <a href="register.php" class="btn btn-small">Enroll Now</a>
                            <?php else: ?>
                                <a href="courses/enroll.php?enroll=<?php echo $course['course_id']; ?>" class="btn btn-small">Enroll Now</a>
                            <?php endif; ?>
                            <a href="course-preview.php?id=<?php echo $course['course_id']; ?>" class="btn-text">Preview</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="section-cta">
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary">Browse All Courses</a>
            <?php else: ?>
                <a href="courses/enroll.php" class="btn btn-primary">Browse All Courses</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials">
    <div class="container">
        <h2 class="section-title">What Our Students Say</h2>
        <p class="section-subtitle">Success stories from our community</p>

        <div class="testimonials-slider">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p class="testimonial-text">"EduSphere completely transformed my career. The courses are well-structured and the instructors are knowledgeable. I landed my dream job after completing the Web Development track!"</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-info">
                        <h4>Sarah Johnson</h4>
                        <p>Web Developer at TechCorp</p>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p class="testimonial-text">"As a busy professional, I needed flexible learning options. EduSphere allowed me to upskill at my own pace. The quality of instruction rivals top universities at a fraction of the cost."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-info">
                        <h4>Michael Chen</h4>
                        <p>Data Scientist at DataWorld</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Start Learning?</h2>
            <p>Join thousands of students advancing their careers with EduSphere</p>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary btn-large">Join Now for Free</a>
            <?php else: ?>
                <a href="courses/enroll.php" class="btn btn-primary btn-large">Browse Courses</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script>
    // Counter animation for stats
    document.addEventListener('DOMContentLoaded', function() {
        const counters = document.querySelectorAll('.stat-number');
        const speed = 200;

        counters.forEach(counter => {
            const target = +counter.getAttribute('data-count');
            const count = +counter.innerText;
            const increment = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(updateCount, 1);
            } else {
                counter.innerText = target;
            }

            function updateCount() {
                const count = +counter.innerText;
                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(updateCount, 1);
                } else {
                    counter.innerText = target;
                }
            }
        });

        // Initialize sliders (you would use a library like Slick or Swiper in production)
        // This is just a placeholder for the concept
        const initSliders = () => {
            console.log('Sliders would be initialized here');
        };
        initSliders();
    });
</script>
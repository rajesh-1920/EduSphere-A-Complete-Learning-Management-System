<?php
require_once 'config.php';
$pageTitle = "Home";
require_once 'header.php';
?>

<section class="hero" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/hero-bg.jpg'); background-size: cover; background-position: center; color: white; padding: 100px 0; text-align: center;">
    <div class="container">
        <h1 style="font-size: 48px; margin-bottom: 20px;">Learn Without Limits</h1>
        <p style="font-size: 20px; margin-bottom: 30px; max-width: 700px; margin-left: auto; margin-right: auto;">Start, switch, or advance your career with more than 5,000 courses, Professional Certificates, and degrees from world-class universities and companies.</p>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <a href="register.php" class="btn btn-primary" style="padding: 15px 30px; font-size: 18px;">Join for Free</a>
            <a href="courses.php" class="btn btn-outline" style="padding: 15px 30px; font-size: 18px; border-color: white; color: white;">Browse Courses</a>
        </div>
    </div>
</section>

<section style="padding: 80px 0;">
    <div class="container">
        <h2 class="text-center" style="margin-bottom: 50px;">Why Choose EduSphere?</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <div class="card" style="text-align: center; padding: 30px;">
                <div style="font-size: 50px; color: var(--primary-color); margin-bottom: 20px;">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h3 style="margin-bottom: 15px;">Learn Anything</h3>
                <p>Explore any interest or trending topic, take prerequisites, and advance your skills.</p>
            </div>

            <div class="card" style="text-align: center; padding: 30px;">
                <div style="font-size: 50px; color: var(--primary-color); margin-bottom: 20px;">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3 style="margin-bottom: 15px;">Expert Instructors</h3>
                <p>Learn from industry experts who are passionate about teaching.</p>
            </div>

            <div class="card" style="text-align: center; padding: 30px;">
                <div style="font-size: 50px; color: var(--primary-color); margin-bottom: 20px;">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3 style="margin-bottom: 15px;">Earn Certificates</h3>
                <p>Get recognized for your work and share your success with others.</p>
            </div>
        </div>
    </div>
</section>

<section style="background-color: #f8f9fa; padding: 80px 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 50px; align-items: center;">
            <div>
                <h2 style="margin-bottom: 20px;">Achieve your goals with EduSphere</h2>
                <p style="margin-bottom: 20px;">Whether you want to advance your career, pursue a passion, or learn something new, EduSphere has courses to help you achieve your goals.</p>
                <ul style="list-style: none;">
                    <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check-circle" style="color: var(--success-color); font-size: 20px;"></i>
                        <span>Learn at your own pace with flexible scheduling</span>
                    </li>
                    <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check-circle" style="color: var(--success-color); font-size: 20px;"></i>
                        <span>Access to high-quality course materials</span>
                    </li>
                    <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check-circle" style="color: var(--success-color); font-size: 20px;"></i>
                        <span>Interactive learning with quizzes and assignments</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check-circle" style="color: var(--success-color); font-size: 20px;"></i>
                        <span>Connect with instructors and peers</span>
                    </li>
                </ul>
            </div>

            <div>
                <img src="assets/learning.jpg" alt="Learning" style="width: 100%; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
            </div>
        </div>
    </div>
</section>

<section style="padding: 80px 0;">
    <div class="container">
        <div class="page-header">
            <h2>Popular Courses</h2>
            <a href="courses.php" class="btn btn-outline">View All Courses</a>
        </div>

        <div class="course-grid">
            <?php
            // Get popular courses (in a real app, you would query courses with most enrollments)
            $courses = getAllCourses();
            $popularCourses = array_slice($courses, 0, 4); // Just get first 4 for demo

            foreach ($popularCourses as $course):
                $instructor = getUserById($course['instructor_id']);
                $enrollmentCount = countEnrolledStudents($course['course_id']);
            ?>
                <div class="card">
                    <img src="<?php echo !empty($course['thumbnail']) ? UPLOAD_DIR . 'courses/' . $course['thumbnail'] : 'assets/course_default.png'; ?>" class="card-img-top" alt="<?php echo $course['title']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $course['title']; ?></h5>
                        <p class="card-text"><?php echo substr($course['description'], 0, 100) . '...'; ?></p>
                        <p class="text-muted mb-0"><small>By <?php echo $instructor['full_name']; ?></small></p>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users"></i> <?php echo $enrollmentCount; ?> students</span>
                        <a href="course_details.php?id=<?php echo $course['course_id']; ?>" class="btn btn-primary btn-sm">View Course</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section style="background-color: var(--primary-color); color: white; padding: 80px 0; text-align: center;">
    <div class="container">
        <h2 style="margin-bottom: 20px;">Ready to start learning?</h2>
        <p style="margin-bottom: 30px; max-width: 700px; margin-left: auto; margin-right: auto;">Join thousands of learners worldwide and start your learning journey today.</p>
        <a href="register.php" class="btn btn-secondary" style="padding: 15px 30px; font-size: 18px;">Get Started</a>
    </div>
</section>

<?php
require_once 'footer.php';
?>
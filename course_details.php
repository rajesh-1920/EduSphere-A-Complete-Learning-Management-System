<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: courses.php");
    exit();
}

$courseId = $_GET['id'];
$course = getCourseById($courseId);

if (!$course) {
    header("Location: courses.php");
    exit();
}

$instructor = getUserById($course['instructor_id']);
$enrollmentCount = countEnrolledStudents($courseId);
$modules = getModules($courseId);
$isEnrolled = isLoggedIn() && isEnrolled($_SESSION['user_id'], $courseId);

$pageTitle = $course['title'];
require_once 'header.php';
?>

<div class="course-details" style="margin-top: 30px;">
    <div class="card">
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
                <div>
                    <h1 style="margin-bottom: 20px;"><?php echo $course['title']; ?></h1>
                    <p style="margin-bottom: 20px;"><?php echo $course['description']; ?></p>
                    
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center;">
                            <img src="<?php echo !empty($instructor['profile_picture']) ? UPLOAD_DIR . 'profile/' . $instructor['profile_picture'] : 'assets/default.png'; ?>" alt="Instructor" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px; object-fit: cover;">
                            <span>Created by <strong><?php echo $instructor['full_name']; ?></strong></span>
                        </div>
                        
                        <div style="display: flex; align-items: center;">
                            <i class="fas fa-users" style="margin-right: 5px;"></i>
                            <span><?php echo $enrollmentCount; ?> students</span>
                        </div>
                        
                        <div style="display: flex; align-items: center;">
                            <i class="fas fa-tag" style="margin-right: 5px;"></i>
                            <span><?php echo $course['category']; ?></span>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 30px;">
                        <h3 style="margin-bottom: 15px;">What you'll learn</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                            <div style="display: flex; align-items: flex-start; gap: 10px;">
                                <i class="fas fa-check-circle" style="color: var(--success-color); margin-top: 3px;"></i>
                                <span>Master the fundamentals of this topic</span>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 10px;">
                                <i class="fas fa-check-circle" style="color: var(--success-color); margin-top: 3px;"></i>
                                <span>Build real-world projects</span>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 10px;">
                                <i class="fas fa-check-circle" style="color: var(--success-color); margin-top: 3px;"></i>
                                <span>Get hands-on experience</span>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 10px;">
                                <i class="fas fa-check-circle" style="color: var(--success-color); margin-top: 3px;"></i>
                                <span>Receive a certificate of completion</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 30px;">
                        <h3 style="margin-bottom: 15px;">Course Content</h3>
                        
                        <?php if (empty($modules)): ?>
                            <p>No modules available yet.</p>
                        <?php else: ?>
                            <div style="border: 1px solid #eee; border-radius: var(--border-radius);">
                                <?php foreach ($modules as $module): 
                                    $lessons = getLessons($module['module_id']);
                                ?>
                                    <div class="module-card">
                                        <div class="module-header">
                                            <div>
                                                <h4 class="module-title"><?php echo $module['title']; ?></h4>
                                                <small class="text-muted"><?php echo count($lessons); ?> lessons</small>
                                            </div>
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                        
                                        <?php if (!empty($lessons)): ?>
                                            <ul class="lesson-list">
                                                <?php foreach ($lessons as $lesson): ?>
                                                    <li class="lesson-item">
                                                        <i class="fas fa-play-circle"></i>
                                                        <span><?php echo $lesson['title']; ?></span>
                                                        <span style="margin-left: auto;"><?php echo $lesson['duration']; ?> min</span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <h3 style="margin-bottom: 15px;">Requirements</h3>
                        <ul style="list-style-position: inside; margin-bottom: 20px;">
                            <li>Basic computer skills</li>
                            <li>Internet connection</li>
                            <li>Willingness to learn</li>
                        </ul>
                    </div>
                </div>
                
                <div>
                    <div class="card" style="position: sticky; top: 20px;">
                        <img src="<?php echo !empty($course['thumbnail']) ? UPLOAD_DIR . 'courses/' . $course['thumbnail'] : 'assets/course_default.png'; ?>" class="card-img-top" alt="<?php echo $course['title']; ?>">
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3 style="margin-bottom: 0;">$<?php echo number_format(99.99, 2); ?></h3>
                                <span class="text-muted"><s>$199.99</s></span>
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                    <span>Includes:</span>
                                </div>
                                <ul style="list-style: none; margin-bottom: 0;">
                                    <li style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-video" style="color: var(--primary-color);"></i>
                                        <span>10 hours on-demand video</span>
                                    </li>
                                    <li style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-file-alt" style="color: var(--primary-color);"></i>
                                        <span>5 articles</span>
                                    </li>
                                    <li style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-download" style="color: var(--primary-color);"></i>
                                        <span>Downloadable resources</span>
                                    </li>
                                    <li style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-mobile-alt" style="color: var(--primary-color);"></i>
                                        <span>Access on mobile and TV</span>
                                    </li>
                                    <li style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-certificate" style="color: var(--primary-color);"></i>
                                        <span>Certificate of completion</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <?php if (isLoggedIn()): ?>
                                <?php if ($isEnrolled): ?>
                                    <a href="course.php?id=<?php echo $courseId; ?>" class="btn btn-primary" style="width: 100%; margin-bottom: 15px;">Continue Learning</a>
                                <?php else: ?>
                                    <form method="POST" action="enroll.php">
                                        <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 15px;">Enroll Now</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php?redirect=course_details.php?id=<?php echo $courseId; ?>" class="btn btn-primary" style="width: 100%; margin-bottom: 15px;">Enroll Now</a>
                            <?php endif; ?>
                            
                            <p class="text-center" style="margin-bottom: 0;">30-Day Money-Back Guarantee</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
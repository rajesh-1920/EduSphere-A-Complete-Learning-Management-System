<?php
require_once 'config.php';
requireLogin();
requireRole('student');

$userId = $_SESSION['user_id'];
$enrolledCourses = getEnrolledCourses($userId);

$pageTitle = "My Courses";
require_once 'header.php';
?>

<div class="dashboard">
    <div class="sidebar">
        <h3 class="sidebar-title">My Learning</h3>
        <ul class="sidebar-menu">
            <li><a href="my_courses.php" class="active"><i class="fas fa-book"></i> My Courses</a></li>
            <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="grades.php"><i class="fas fa-chart-bar"></i> Grades</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h2>My Courses</h2>
            <a href="courses.php" class="btn btn-primary">Browse Courses</a>
        </div>
        
        <?php if (empty($enrolledCourses)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <h4>You haven't enrolled in any courses yet</h4>
                    <p>Browse our course catalog to find something that interests you</p>
                    <a href="courses.php" class="btn btn-primary">Browse Courses</a>
                </div>
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($enrolledCourses as $course): 
                    $progress = getCourseProgress($userId, $course['course_id']);
                ?>
                    <div class="card">
                        <img src="<?= !empty($course['thumbnail']) ? 
                                  UPLOAD_DIR . 'courses/' . $course['thumbnail'] : 
                                  'assets/course_default.png' ?>" 
                             class="card-img-top" alt="<?= htmlspecialchars($course['title']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars(substr($course['description'], 0, 100)) ?>...</p>
                            
                            <div class="progress-container mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Progress</small>
                                    <small><?= $progress ?>%</small>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?= $progress ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="course.php?id=<?= $course['course_id'] ?>" class="btn btn-primary btn-block">
                                Continue Learning
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
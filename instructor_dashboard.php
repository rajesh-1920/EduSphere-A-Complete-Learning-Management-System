<?php
require_once 'config.php';
requireRole('instructor');

$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$courses = getAllCourses($userId);

$pageTitle = "Instructor Dashboard";
require_once 'header.php';
?>

<div class="dashboard">
    <div class="sidebar">
        <h3 class="sidebar-title">Instructor Dashboard</h3>
        <ul class="sidebar-menu">
            <li><a href="instructor_dashboard.php" class="active"><i class="fas fa-home"></i> Overview</a></li>
            <li><a href="instructor_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
            <li><a href="create_course.php"><i class="fas fa-plus-circle"></i> Create Course</a></li>
            <li><a href="instructor_assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="instructor_students.php"><i class="fas fa-users"></i> Students</a></li>
            <li><a href="instructor_announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h2>Welcome, <?php echo $user['full_name']; ?>!</h2>
            <a href="create_course.php" class="btn btn-primary">Create New Course</a>
        </div>
        
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-body">
                <h3 style="margin-bottom: 20px;">Teaching Overview</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div class="card" style="text-align: center; padding: 20px;">
                        <h4 style="margin-bottom: 10px;">Total Courses</h4>
                        <p style="font-size: 32px; font-weight: bold; color: var(--primary-color);"><?php echo count($courses); ?></p>
                    </div>
                    
                    <div class="card" style="text-align: center; padding: 20px;">
                        <h4 style="margin-bottom: 10px;">Total Students</h4>
                        <p style="font-size: 32px; font-weight: bold; color: var(--primary-color);">
                            <?php 
                            $totalStudents = 0;
                            foreach ($courses as $course) {
                                $totalStudents += countEnrolledStudents($course['course_id']);
                            }
                            echo $totalStudents;
                            ?>
                        </p>
                    </div>
                    
                    <div class="card" style="text-align: center; padding: 20px;">
                        <h4 style="margin-bottom: 10px;">Assignments to Grade</h4>
                        <p style="font-size: 32px; font-weight: bold; color: var(--primary-color);">0</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-body">
                <div class="page-header">
                    <h3>Your Courses</h3>
                    <a href="instructor_courses.php" class="btn btn-outline">View All</a>
                </div>
                
                <?php if (empty($courses)): ?>
                    <p>You haven't created any courses yet.</p>
                    <a href="create_course.php" class="btn btn-primary">Create Your First Course</a>
                <?php else: ?>
                    <div class="course-grid">
                        <?php foreach (array_slice($courses, 0, 4) as $course): 
                            $studentsCount = countEnrolledStudents($course['course_id']);
                        ?>
                            <div class="card">
                                <img src="<?php echo !empty($course['thumbnail']) ? UPLOAD_DIR . 'courses/' . $course['thumbnail'] : 'assets/course_default.png'; ?>" class="card-img-top" alt="<?php echo $course['title']; ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $course['title']; ?></h5>
                                    <p class="card-text"><?php echo substr($course['description'], 0, 100) . '...'; ?></p>
                                    <p class="text-muted mb-0"><small><?php echo $studentsCount; ?> students enrolled</small></p>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <a href="instructor_course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-primary btn-sm">Manage</a>
                                    <span class="badge badge-secondary"><?php echo $course['category']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="page-header">
                    <h3>Recent Announcements</h3>
                    <a href="instructor_announcements.php" class="btn btn-outline">View All</a>
                </div>
                
                <?php
                // Get recent announcements (in a real app, you would query announcements)
                $recentAnnouncements = [];
                ?>
                
                <?php if (empty($recentAnnouncements)): ?>
                    <p>No recent announcements.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAnnouncements as $announcement): ?>
                                    <tr>
                                        <td><?php echo $announcement['course_title']; ?></td>
                                        <td><?php echo $announcement['title']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($announcement['created_at'])); ?></td>
                                        <td>
                                            <a href="announcement.php?id=<?php echo $announcement['announcement_id']; ?>" class="btn btn-primary btn-sm">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
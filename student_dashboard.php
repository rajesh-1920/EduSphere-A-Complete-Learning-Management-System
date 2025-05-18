<?php
require_once 'config.php';
requireRole('student');

$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$enrolledCourses = getEnrolledCourses($userId);

$pageTitle = "Student Dashboard";
require_once 'header.php';
?>

<div class="dashboard">
    <div class="sidebar">
        <h3 class="sidebar-title">Dashboard</h3>
        <ul class="sidebar-menu">
            <li><a href="student_dashboard.php" class="active"><i class="fas fa-home"></i> Overview</a></li>
            <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
            <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="grades.php"><i class="fas fa-chart-bar"></i> Grades</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h2>Welcome back, <?php echo $user['full_name']; ?>!</h2>
            <a href="courses.php" class="btn btn-primary">Browse Courses</a>
        </div>
        
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-body">
                <h3 style="margin-bottom: 20px;">Your Learning Progress</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div class="card" style="text-align: center; padding: 20px;">
                        <h4 style="margin-bottom: 10px;">Enrolled Courses</h4>
                        <p style="font-size: 32px; font-weight: bold; color: var(--primary-color);"><?php echo count($enrolledCourses); ?></p>
                    </div>
                    
                    <div class="card" style="text-align: center; padding: 20px;">
                        <h4 style="margin-bottom: 10px;">Courses in Progress</h4>
                        <p style="font-size: 32px; font-weight: bold; color: var(--primary-color);"><?php echo count($enrolledCourses); ?></p>
                    </div>
                    
                    <div class="card" style="text-align: center; padding: 20px;">
                        <h4 style="margin-bottom: 10px;">Completed Courses</h4>
                        <p style="font-size: 32px; font-weight: bold; color: var(--primary-color);">0</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-body">
                <div class="page-header">
                    <h3>Your Courses</h3>
                    <a href="my_courses.php" class="btn btn-outline">View All</a>
                </div>
                
                <?php if (empty($enrolledCourses)): ?>
                    <p>You haven't enrolled in any courses yet.</p>
                    <a href="courses.php" class="btn btn-primary">Browse Courses</a>
                <?php else: ?>
                    <div class="course-grid">
                        <?php foreach (array_slice($enrolledCourses, 0, 4) as $course): 
                            $progress = getCourseProgress($userId, $course['course_id']);
                        ?>
                            <div class="card">
                                <img src="<?php echo !empty($course['thumbnail']) ? UPLOAD_DIR . 'courses/' . $course['thumbnail'] : 'assets/course_default.png'; ?>" class="card-img-top" alt="<?php echo $course['title']; ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $course['title']; ?></h5>
                                    <p class="card-text"><?php echo substr($course['description'], 0, 100) . '...'; ?></p>
                                    
                                    <div style="margin-bottom: 15px;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                            <small>Progress</small>
                                            <small><?php echo $progress; ?>%</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-primary btn-sm" style="width: 100%;">Continue Learning</a>
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
                    <h3>Upcoming Assignments</h3>
                    <a href="assignments.php" class="btn btn-outline">View All</a>
                </div>
                
                <?php
                // Get upcoming assignments (in a real app, you would query assignments with due dates)
                $upcomingAssignments = [];
                ?>
                
                <?php if (empty($upcomingAssignments)): ?>
                    <p>No upcoming assignments.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Assignment</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingAssignments as $assignment): ?>
                                    <tr>
                                        <td><?php echo $assignment['course_title']; ?></td>
                                        <td><?php echo $assignment['title']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></td>
                                        <td>
                                            <span class="badge badge-warning">Pending</span>
                                        </td>
                                        <td>
                                            <a href="assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-primary btn-sm">View</a>
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
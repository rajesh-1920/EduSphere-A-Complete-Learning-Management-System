<?php
require_once 'config.php';
requireRole('admin');

// Get statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalInstructors = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'instructor'")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();

// Get recent users
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Get recent courses
$recentCourses = $pdo->query("SELECT c.*, u.full_name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.user_id ORDER BY c.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Admin Dashboard";
require_once 'header.php';
?>

<div class="dashboard">
    <div class="sidebar">
        <h3 class="sidebar-title">Admin Dashboard</h3>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="manage_courses.php"><i class="fas fa-book"></i> Courses</a></li>
            <li><a href="manage_enrollments.php"><i class="fas fa-user-graduate"></i> Enrollments</a></li>
            <li><a href="system_settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h2>Admin Dashboard</h2>
        </div>
        
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-body">
                <h3 style="margin-bottom: 20px;">System Overview</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="card" style="text-align: center; padding: 20px; background-color: var(--primary-color); color: white;">
                        <h4 style="margin-bottom: 10px;">Total Users</h4>
                        <p style="font-size: 32px; font-weight: bold;"><?php echo $totalUsers; ?></p>
                    </div>
                    
                    <div class="card" style="text-align: center; padding: 20px; background-color: var(--success-color); color: white;">
                        <h4 style="margin-bottom: 10px;">Students</h4>
                        <p style="font-size: 32px; font-weight: bold;"><?php echo $totalStudents; ?></p>
                    </div>
                    
                    <div class="card" style="text-align: center; padding: 20px; background-color: var(--info-color); color: white;">
                        <h4 style="margin-bottom: 10px;">Instructors</h4>
                        <p style="font-size: 32px; font-weight: bold;"><?php echo $totalInstructors; ?></p>
                    </div>
                    
                    <div class="card" style="text-align: center; padding: 20px; background-color: var(--warning-color); color: white;">
                        <h4 style="margin-bottom: 10px;">Courses</h4>
                        <p style="font-size: 32px; font-weight: bold;"><?php echo $totalCourses; ?></p>
                    </div>
                    
                    <div class="card" style="text-align: center; padding: 20px; background-color: var(--secondary-color); color: white;">
                        <h4 style="margin-bottom: 10px;">Enrollments</h4>
                        <p style="font-size: 32px; font-weight: bold;"><?php echo $totalEnrollments; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
            <div class="card">
                <div class="card-body">
                    <div class="page-header">
                        <h3>Recent Users</h3>
                        <a href="manage_users.php" class="btn btn-outline">View All</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td><?php echo $user['full_name']; ?></td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td><?php echo ucfirst($user['role']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="page-header">
                        <h3>Recent Courses</h3>
                        <a href="manage_courses.php" class="btn btn-outline">View All</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Instructor</th>
                                    <th>Category</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentCourses as $course): ?>
                                    <tr>
                                        <td><?php echo $course['title']; ?></td>
                                        <td><?php echo $course['instructor_name']; ?></td>
                                        <td><?php echo $course['category']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($course['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: 20px;">Quick Actions</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <a href="add_user.php" class="card" style="text-align: center; padding: 20px; text-decoration: none; color: inherit;">
                        <div style="font-size: 40px; color: var(--primary-color); margin-bottom: 10px;">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h4>Add User</h4>
                    </a>
                    
                    <a href="create_course.php" class="card" style="text-align: center; padding: 20px; text-decoration: none; color: inherit;">
                        <div style="font-size: 40px; color: var(--primary-color); margin-bottom: 10px;">
                            <i class="fas fa-book-medical"></i>
                        </div>
                        <h4>Create Course</h4>
                    </a>
                    
                    <a href="system_settings.php" class="card" style="text-align: center; padding: 20px; text-decoration: none; color: inherit;">
                        <div style="font-size: 40px; color: var(--primary-color); margin-bottom: 10px;">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h4>System Settings</h4>
                    </a>
                    
                    <a href="reports.php" class="card" style="text-align: center; padding: 20px; text-decoration: none; color: inherit;">
                        <div style="font-size: 40px; color: var(--primary-color); margin-bottom: 10px;">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h4>Generate Reports</h4>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
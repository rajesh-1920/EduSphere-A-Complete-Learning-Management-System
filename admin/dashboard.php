<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
checkRole(['admin']);

$pageTitle = 'Admin Dashboard';
require_once '../includes/header.php';

// Get statistics
$db = new Database();
$usersCount = $db->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$coursesCount = $db->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];
$enrollmentsCount = $db->query("SELECT COUNT(*) FROM enrollments")->fetch_row()[0];
?>

<div class="dashboard">
    <h1>Admin Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Users</h3>
            <p><?php echo $usersCount; ?></p>
            <a href="users/manage_users.php">View All</a>
        </div>
        <div class="stat-card">
            <h3>Total Courses</h3>
            <p><?php echo $coursesCount; ?></p>
            <a href="courses/manage_courses.php">View All</a>
        </div>
        <div class="stat-card">
            <h3>Total Enrollments</h3>
            <p><?php echo $enrollmentsCount; ?></p>
            <a href="reports/enrollment_report.php">View Report</a>
        </div>
    </div>
    
    <div class="recent-activity">
        <h2>Recent Activity</h2>
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Activity</th>
                </tr>
            </thead>
            <tbody>
                <!-- Would be populated from a real activity log table -->
                <tr>
                    <td>Just now</td>
                    <td>System</td>
                    <td>Admin dashboard accessed</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
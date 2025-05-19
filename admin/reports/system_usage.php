<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['admin']);

$db = new Database();

// Get user statistics
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$admins = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetch_row()[0];
$instructors = $db->query("SELECT COUNT(*) FROM users WHERE role = 'instructor'")->fetch_row()[0];
$students = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0];

// Get recent users
$recentUsers = $db->query("
    SELECT username, first_name, last_name, role, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get activity statistics (this would normally come from an activity log table)
$activeCourses = $db->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];
$assignments = $db->query("SELECT COUNT(*) FROM assignments")->fetch_row()[0];
$quizzes = $db->query("SELECT COUNT(*) FROM quizzes")->fetch_row()[0];

$pageTitle = 'System Usage Report';
require_once '../../includes/header.php';
?>

<div class="system-usage-report">
    <h1>System Usage Report</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Users</h3>
            <p><?php echo $totalUsers; ?></p>
        </div>
        <div class="stat-card">
            <h3>Admins</h3>
            <p><?php echo $admins; ?></p>
        </div>
        <div class="stat-card">
            <h3>Instructors</h3>
            <p><?php echo $instructors; ?></p>
        </div>
        <div class="stat-card">
            <h3>Students</h3>
            <p><?php echo $students; ?></p>
        </div>
        <div class="stat-card">
            <h3>Active Courses</h3>
            <p><?php echo $activeCourses; ?></p>
        </div>
        <div class="stat-card">
            <h3>Assignments</h3>
            <p><?php echo $assignments; ?></p>
        </div>
        <div class="stat-card">
            <h3>Quizzes</h3>
            <p><?php echo $quizzes; ?></p>
        </div>
    </div>
    
    <div class="report-section">
        <h2>Recent Users</h2>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td><?php echo formatDate($user['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
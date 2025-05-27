<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$db = new Database();

// Get announcements for courses taught by this instructor
$announcements = $db->query("
    SELECT a.*, c.title as course_title
    FROM announcements a
    JOIN courses c ON a.course_id = c.course_id
    WHERE c.instructor_id = $instructorId
    ORDER BY a.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Announcements';
require_once '../../includes/header.php';
?>

<div class="manage-announcements">
    <h1>Manage Announcements</h1>

    <div class="action-bar">
        <a href="add_announcement.php" class="btn">Create New Announcement</a>
    </div>

    <?php if (!empty($announcements)): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($announcements as $announcement): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                        <td><?php echo htmlspecialchars($announcement['course_title']); ?></td>
                        <td><?php echo formatDate($announcement['created_at']); ?></td>
                        <td>
                            <span class="status-badge <?php echo (strtotime($announcement['created_at']) > strtotime('-7 days')) ? 'active' : 'inactive'; ?>">
                                <?php echo (strtotime($announcement['created_at']) > strtotime('-7 days')) ? 'Active' : 'Archived'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_announcement.php?id=<?php echo $announcement['announcement_id']; ?>" class="btn btn-small">Edit</a>
                            <a href="delete_announcement.php?id=<?php echo $announcement['announcement_id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this announcement?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-results">
            <p>You haven't created any announcements yet.</p>
            <a href="add_announcement.php" class="btn">Create Your First Announcement</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
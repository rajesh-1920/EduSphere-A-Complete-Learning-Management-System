<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('manage_announcements.php');
}

$announcementId = $_GET['id'];
$instructorId = $_SESSION['user_id'];
$db = new Database();

// Get announcement data
$announcement = $db->query("
    SELECT a.*, c.title as course_title, u.first_name, u.last_name
    FROM announcements a
    JOIN courses c ON a.course_id = c.course_id
    JOIN users u ON a.author_id = u.user_id
    WHERE a.announcement_id = $announcementId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$announcement) {
    redirect('manage_announcements.php');
}

$pageTitle = $announcement['title'];
require_once '../../includes/header.php';
?>

<div class="view-announcement">
    <div class="announcement-header">
        <div class="breadcrumb">
            <a href="manage_announcements.php">Announcements</a> &raquo;
            <span>View Announcement</span>
        </div>

        <div class="announcement-actions">
            <a href="edit_announcement.php?id=<?php echo $announcementId; ?>" class="btn btn-small">Edit</a>
            <a href="delete_announcement.php?id=<?php echo $announcementId; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this announcement?')">Delete</a>
        </div>
    </div>

    <div class="announcement-card">
        <div class="announcement-meta">
            <span class="course"><?php echo htmlspecialchars($announcement['course_title']); ?></span>
            <span class="date"><?php echo formatDate($announcement['created_at']); ?></span>
            <?php if ($announcement['is_important']): ?>
                <span class="badge important">Important</span>
            <?php endif; ?>
        </div>

        <h1><?php echo htmlspecialchars($announcement['title']); ?></h1>

        <div class="announcement-content">
            <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
        </div>

        <div class="announcement-footer">
            <div class="author">
                Posted by: <?php echo htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']); ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
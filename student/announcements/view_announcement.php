<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

if (!isset($_GET['id'])) {
    redirect('view_announcements.php');
}

$announcementId = (int)$_GET['id'];
$studentId = $_SESSION['user_id'];
$db = new Database();

// Get announcement details
$announcement = $db->query("
    SELECT a.*, c.title as course_title, u.first_name, u.last_name
    FROM announcements a
    JOIN courses c ON a.course_id = c.course_id
    JOIN users u ON a.author_id = u.user_id
    JOIN enrollments e ON a.course_id = c.course_id
    WHERE a.announcement_id = $announcementId
    AND e.student_id = $studentId
    AND e.status = 'active'
")->fetch_assoc();

if (!$announcement) {
    redirect('view_announcements.php');
}

$pageTitle = htmlspecialchars($announcement['title']);
require_once '../../includes/header.php';
?>

<div class="view-announcement">
    <div class="announcement-header">
        <h1><?php echo htmlspecialchars($announcement['title']); ?></h1>
        <div class="announcement-meta">
            <span class="course">Course: <?php echo htmlspecialchars($announcement['course_title']); ?></span>
            <span class="author">Posted by <?php echo htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']); ?></span>
            <span class="date"><?php echo formatDate($announcement['created_at']); ?></span>
        </div>
    </div>
    
    <div class="announcement-content">
        <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
    </div>
    
    <?php if (!empty($announcement['file_path'])): ?>
        <div class="announcement-attachment">
            <h4>Attachment:</h4>
            <a href="<?php echo SITE_URL; ?>/uploads/announcements/<?php echo $announcement['file_path']; ?>" 
               class="btn btn-outline-primary" download>
                Download File
            </a>
        </div>
    <?php endif; ?>
    
    <div class="announcement-actions mt-4">
        <a href="view_announcements.php" class="btn btn-primary">Back to Announcements</a>
        <a href="../courses/view_course.php?id=<?php echo $announcement['course_id']; ?>" class="btn btn-secondary">Go to Course</a>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

if (!isset($_GET['id'])) {
    redirect('view_discussions.php');
}

$discussionId = (int)$_GET['id'];
$studentId = $_SESSION['user_id'];
$db = new Database();

// Get discussion details
$discussion = $db->query("
    SELECT d.*, c.title as course_title, u.first_name, u.last_name
    FROM discussions d
    JOIN courses c ON d.course_id = c.course_id
    JOIN users u ON d.author_id = u.user_id
    JOIN enrollments e ON d.course_id = c.course_id
    WHERE d.discussion_id = $discussionId
    AND e.student_id = $studentId
    AND e.status = 'active'
")->fetch_assoc();

if (!$discussion) {
    redirect('view_discussions.php');
}

// Get replies
$replies = $db->query("
    SELECT r.*, u.first_name, u.last_name, u.profile_picture
    FROM discussion_replies r
    JOIN users u ON r.author_id = u.user_id
    WHERE r.discussion_id = $discussionId
    ORDER BY r.created_at ASC
")->fetch_all(MYSQLI_ASSOC);

// Process reply form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = sanitize($_POST['content']);
    
    if (!empty($content)) {
        $stmt = $db->prepare("
            INSERT INTO discussion_replies (discussion_id, author_id, content)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $discussionId, $studentId, $content);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Reply posted successfully!";
            redirect("view_discussion.php?id=$discussionId");
        } else {
            $_SESSION['error'] = "Failed to post reply. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Reply content cannot be empty.";
    }
}

$pageTitle = htmlspecialchars($discussion['title']);
require_once '../../includes/header.php';
?>

<div class="view-discussion">
    <div class="discussion-header">
        <h1><?php echo htmlspecialchars($discussion['title']); ?></h1>
        <div class="discussion-meta">
            <span class="course">Course: <?php echo htmlspecialchars($discussion['course_title']); ?></span>
            <span class="author">Started by <?php echo htmlspecialchars($discussion['first_name'] . ' ' . $discussion['last_name']); ?></span>
            <span class="date"><?php echo formatDate($discussion['created_at']); ?></span>
        </div>
    </div>
    
    <div class="discussion-content">
        <?php echo nl2br(htmlspecialchars($discussion['content'])); ?>
    </div>
    
    <div class="discussion-replies">
        <h2>Replies (<?php echo count($replies); ?>)</h2>
        
        <?php if (!empty($replies)): ?>
            <div class="replies-list">
                <?php foreach ($replies as $reply): ?>
                    <div class="reply-card">
                        <div class="reply-author">
                            <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $reply['profile_picture'] ?? 'default.png'; ?>" 
                                 alt="<?php echo htmlspecialchars($reply['first_name'] . ' ' . $reply['last_name']); ?>">
                            <div class="author-info">
                                <h4><?php echo htmlspecialchars($reply['first_name'] . ' ' . $reply['last_name']); ?></h4>
                                <p class="date"><?php echo formatDate($reply['created_at']); ?></p>
                            </div>
                        </div>
                        <div class="reply-content">
                            <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No replies yet. Be the first to reply!</p>
        <?php endif; ?>
        
        <div class="add-reply">
            <h3>Add Your Reply</h3>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <textarea name="content" class="form-control" rows="5" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Post Reply</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="discussion-actions">
        <a href="view_discussions.php" class="btn btn-secondary">Back to Discussions</a>
        <a href="../courses/view_course.php?id=<?php echo $discussion['course_id']; ?>" class="btn btn-primary">Go to Course</a>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
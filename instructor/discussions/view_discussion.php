<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('manage_discussions.php');
}

$discussionId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get discussion data and verify ownership
$discussion = $db->query("
    SELECT d.*, c.title as course_title, u.first_name, u.last_name
    FROM discussions d
    JOIN courses c ON d.course_id = c.course_id
    JOIN users u ON d.author_id = u.user_id
    WHERE d.discussion_id = $discussionId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$discussion) {
    redirect('manage_discussions.php');
}

// Get replies for this discussion
$replies = $db->query("
    SELECT r.*, u.first_name, u.last_name, u.profile_picture
    FROM discussion_replies r
    JOIN users u ON r.author_id = u.user_id
    WHERE r.discussion_id = $discussionId
    ORDER BY r.created_at ASC
")->fetch_all(MYSQLI_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = sanitize($_POST['content']);

    if (empty($content)) {
        $errors[] = 'Reply content is required';
    }

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO discussion_replies (discussion_id, author_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $discussionId, $instructorId, $content);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Reply added successfully!';
            redirect("view_discussion.php?id=$discussionId");
        } else {
            $errors[] = 'Failed to add reply. Please try again.';
        }
    }
}

$pageTitle = 'View Discussion';
require_once '../../includes/header.php';
?>

<div class="view-discussion">
    <h1><?php echo htmlspecialchars($discussion['title']); ?></h1>
    <p>Course: <?php echo htmlspecialchars($discussion['course_title']); ?></p>
    <p>Started by: <?php echo htmlspecialchars($discussion['first_name'] . ' ' . $discussion['last_name']); ?></p>
    <p>Date: <?php echo formatDate($discussion['created_at']); ?></p>
    
    <div class="discussion-content">
        <div class="content-box">
            <?php echo nl2br(htmlspecialchars($discussion['content'])); ?>
        </div>
    </div>
    
    <div class="discussion-replies">
        <h2>Replies (<?php echo count($replies); ?>)</h2>
        
        <?php if (!empty($replies)): ?>
            <?php foreach ($replies as $reply): ?>
                <div class="reply-card">
                    <div class="reply-author">
                        <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $reply['profile_picture'] ?? 'default.png'; ?>" alt="Profile" width="50">
                        <div>
                            <strong><?php echo htmlspecialchars($reply['first_name'] . ' ' . $reply['last_name']); ?></strong>
                            <small><?php echo formatDate($reply['created_at']); ?></small>
                        </div>
                    </div>
                    <div class="reply-content">
                        <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No replies yet.</p>
        <?php endif; ?>
    </div>
    
    <div class="add-reply">
        <h3>Add Reply</h3>
        
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <textarea id="content" name="content" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn">Post Reply</button>
        </form>
    </div>
    
    <div class="action-bar">
        <a href="manage_discussions.php" class="btn">Back to Discussions</a>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
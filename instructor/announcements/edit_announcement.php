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

// Fetch announcement data
$stmt = $db->prepare("
    SELECT a.*, c.title as course_title 
    FROM announcements a
    JOIN courses c ON a.course_id = c.course_id
    WHERE a.announcement_id = ? AND c.instructor_id = ?
");
$stmt->bind_param("ii", $announcementId, $instructorId);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();

if (!$announcement) {
    redirect('manage_announcements.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);

    // Validate inputs
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($content)) $errors[] = 'Content is required';

    if (empty($errors)) {
        $stmt = $db->prepare("
            UPDATE announcements 
            SET title = ?, content = ?, updated_at = NOW()
            WHERE announcement_id = ?
        ");
        $stmt->bind_param("ssi", $title, $content, $announcementId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Announcement updated successfully!';
            redirect('manage_announcements.php');
        } else {
            $errors[] = 'Failed to update announcement. Please try again.';
        }
    }
}

$pageTitle = 'Edit Announcement';
require_once '../../includes/header.php';
?>

<div class="edit-announcement-form">
    <h1>Edit Announcement: <?php echo htmlspecialchars($announcement['title']); ?></h1>
    <p>Course: <?php echo htmlspecialchars($announcement['course_title']); ?></p>

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
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($announcement['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="6" required><?php echo htmlspecialchars($announcement['content']); ?></textarea>
        </div>
        <button type="submit" class="btn">Update Announcement</button>
        <a href="manage_announcements.php" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
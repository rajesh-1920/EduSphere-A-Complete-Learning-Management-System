<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('../../manage_courses.php');
}

$lessonId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get lesson data and verify ownership
$lesson = $db->query("
    SELECT l.*, m.title as module_title, c.title as course_title 
    FROM lessons l
    JOIN modules m ON l.module_id = m.module_id
    JOIN courses c ON m.course_id = c.course_id
    WHERE l.lesson_id = $lessonId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$lesson) {
    redirect('../../manage_courses.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $videoUrl = sanitize($_POST['video_url']);
    $position = (int)$_POST['position'];

    // Validate inputs
    if (empty($title)) $errors[] = 'Title is required';
    if ($position <= 0) $errors[] = 'Position must be a positive number';

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE lessons SET title = ?, content = ?, video_url = ?, position = ? WHERE lesson_id = ?");
        $stmt->bind_param("sssii", $title, $content, $videoUrl, $position, $lessonId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Lesson updated successfully!';
            redirect("manage_lessons.php?module_id={$lesson['module_id']}");
        } else {
            $errors[] = 'Failed to update lesson. Please try again.';
        }
    }
}

$pageTitle = 'Edit Lesson';
require_once '../../../includes/header.php';
?>

<div class="edit-lesson-form">
    <h1>Edit Lesson</h1>
    <p>Course: <?php echo htmlspecialchars($lesson['course_title']); ?></p>
    <p>Module: <?php echo htmlspecialchars($lesson['module_title']); ?></p>
    
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
            <label for="title">Lesson Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="10" class="rich-text"><?php echo htmlspecialchars($lesson['content']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="video_url">Video URL (optional)</label>
            <input type="url" id="video_url" name="video_url" value="<?php echo htmlspecialchars($lesson['video_url']); ?>" placeholder="https://youtube.com/watch?v=...">
        </div>
        <div class="form-group">
            <label for="position">Position in Module</label>
            <input type="number" id="position" name="position" value="<?php echo $lesson['position']; ?>" min="1" required>
        </div>
        <button type="submit" class="btn">Update Lesson</button>
        <a href="manage_lessons.php?module_id=<?php echo $lesson['module_id']; ?>" class="btn">Cancel</a>
    </form>
</div>

<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '.rich-text',
    plugins: 'advlist link image lists code',
    toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code',
    height: 300
});
</script>

<?php require_once '../../../includes/footer.php'; ?>
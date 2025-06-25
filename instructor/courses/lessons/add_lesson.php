<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['module_id'])) {
    redirect('../../manage_courses.php');
}

$moduleId = $_GET['module_id'];
$instructorId = $_SESSION['user_id'];

// Verify instructor owns this module
$module = $db->query("
    SELECT m.*, c.title as course_title 
    FROM modules m
    JOIN courses c ON m.course_id = c.course_id
    WHERE m.module_id = $moduleId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$module) {
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
        $stmt = $db->prepare("INSERT INTO lessons (module_id, title, content, video_url, position) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $moduleId, $title, $content, $videoUrl, $position);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Lesson added successfully!';
            redirect("manage_lessons.php?module_id=$moduleId");
        } else {
            $errors[] = 'Failed to add lesson. Please try again.';
        }
    }
}

// Get next available position
$nextPosition = $db->query("SELECT IFNULL(MAX(position), 0) + 1 FROM lessons WHERE module_id = $moduleId")->fetch_row()[0];

$pageTitle = 'Add Lesson';
require_once '../../../includes/header.php';
?>

<div class="add-lesson-form">
    <h1>Add Lesson</h1>
    <p>Course: <?php echo htmlspecialchars($module['course_title']); ?></p>
    <p>Module: <?php echo htmlspecialchars($module['title']); ?></p>
    
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
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="10" class="rich-text"></textarea>
        </div>
        <div class="form-group">
            <label for="video_url">Video URL (optional)</label>
            <input type="url" id="video_url" name="video_url" placeholder="https://youtube.com/watch?v=...">
        </div>
        <div class="form-group">
            <label for="position">Position in Module</label>
            <input type="number" id="position" name="position" value="<?php echo $nextPosition; ?>" min="1" required>
        </div>
        <button type="submit" class="btn">Add Lesson</button>
        <a href="manage_lessons.php?module_id=<?php echo $moduleId; ?>" class="btn">Cancel</a>
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
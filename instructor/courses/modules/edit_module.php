<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('../manage_courses.php');
}

$moduleId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get module data and verify ownership
$module = $db->query("
    SELECT m.* 
    FROM modules m
    JOIN courses c ON m.course_id = c.course_id
    WHERE m.module_id = $moduleId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$module) {
    redirect('../manage_courses.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $position = (int)$_POST['position'];

    // Validate inputs
    if (empty($title)) $errors[] = 'Title is required';
    if ($position <= 0) $errors[] = 'Position must be a positive number';

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE modules SET title = ?, description = ?, position = ? WHERE module_id = ?");
        $stmt->bind_param("ssii", $title, $description, $position, $moduleId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Module updated successfully!';
            redirect("manage_modules.php?course_id={$module['course_id']}");
        } else {
            $errors[] = 'Failed to update module. Please try again.';
        }
    }
}

$pageTitle = 'Edit Module';
require_once '../../../includes/header.php';
?>

<div class="edit-module-form">
    <h1>Edit Module</h1>
    <p>Course: <?php echo getCourseById($module['course_id'])['title']; ?></p>
    
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
            <label for="title">Module Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($module['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($module['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="position">Position in Course</label>
            <input type="number" id="position" name="position" value="<?php echo $module['position']; ?>" min="1" required>
        </div>
        <button type="submit" class="btn">Update Module</button>
        <a href="manage_modules.php?course_id=<?php echo $module['course_id']; ?>" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../../includes/footer.php'; ?>
<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['course_id'])) {
    redirect('../manage_courses.php');
}

$courseId = $_GET['course_id'];
$instructorId = $_SESSION['user_id'];

// Verify instructor owns this course
$course = $db->query("SELECT course_id FROM courses WHERE course_id = $courseId AND instructor_id = $instructorId")->fetch_row();
if (!$course) {
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
        $stmt = $db->prepare("INSERT INTO modules (course_id, title, description, position) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $courseId, $title, $description, $position);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Module added successfully!';
            redirect("manage_modules.php?course_id=$courseId");
        } else {
            $errors[] = 'Failed to add module. Please try again.';
        }
    }
}

// Get next available position
$nextPosition = $db->query("SELECT IFNULL(MAX(position), 0) + 1 FROM modules WHERE course_id = $courseId")->fetch_row()[0];

$pageTitle = 'Add Module';
require_once '../../../includes/header.php';
?>

<div class="add-module-form">
    <h1>Add Module</h1>
    <p>Course: <?php echo getCourseById($courseId)['title']; ?></p>
    
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
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        <div class="form-group">
            <label for="position">Position in Course</label>
            <input type="number" id="position" name="position" value="<?php echo $nextPosition; ?>" min="1" required>
        </div>
        <button type="submit" class="btn">Add Module</button>
        <a href="manage_modules.php?course_id=<?php echo $courseId; ?>" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../../includes/footer.php'; ?>
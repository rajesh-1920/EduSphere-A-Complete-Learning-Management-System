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

// Get lessons for this module
$lessons = $db->query("
    SELECT * FROM lessons 
    WHERE module_id = $moduleId
    ORDER BY position ASC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Lessons';
require_once '../../../includes/header.php';
?>

<div class="manage-lessons">
    <h1>Manage Lessons</h1>
    <p>Course: <?php echo htmlspecialchars($module['course_title']); ?></p>
    <p>Module: <?php echo htmlspecialchars($module['title']); ?></p>
    
    <div class="action-bar">
        <a href="add_lesson.php?module_id=<?php echo $moduleId; ?>" class="btn">Add Lesson</a>
        <a href="../manage_modules.php?course_id=<?php echo $module['course_id']; ?>" class="btn">Back to Modules</a>
    </div>
    
    <?php if (!empty($lessons)): ?>
        <table>
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Title</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lessons as $lesson): ?>
                    <tr>
                        <td><?php echo $lesson['position']; ?></td>
                        <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                        <td>
                            <a href="edit_lesson.php?id=<?php echo $lesson['lesson_id']; ?>" class="btn">Edit</a>
                            <a href="delete_lesson.php?id=<?php echo $lesson['lesson_id']; ?>" class="btn delete-btn">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No lessons added yet. <a href="add_lesson.php?module_id=<?php echo $moduleId; ?>">Add your first lesson</a></p>
    <?php endif; ?>
</div>

<?php require_once '../../../includes/footer.php'; ?>
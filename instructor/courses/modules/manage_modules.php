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

// Get modules for this course
$modules = $db->query("
    SELECT * FROM modules 
    WHERE course_id = $courseId
    ORDER BY position ASC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Modules';
require_once '../../../includes/header.php';
?>

<div class="manage-modules">
    <h1>Manage Modules</h1>
    <p>Course: <?php echo getCourseById($courseId)['title']; ?></p>
    
    <div class="action-bar">
        <a href="add_module.php?course_id=<?php echo $courseId; ?>" class="btn">Add Module</a>
        <a href="../view_course.php?id=<?php echo $courseId; ?>" class="btn">Back to Course</a>
    </div>
    
    <?php if (!empty($modules)): ?>
        <table>
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Title</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $module): ?>
                    <tr>
                        <td><?php echo $module['position']; ?></td>
                        <td><?php echo htmlspecialchars($module['title']); ?></td>
                        <td>
                            <a href="edit_module.php?id=<?php echo $module['module_id']; ?>" class="btn">Edit</a>
                            <a href="delete_module.php?id=<?php echo $module['module_id']; ?>" class="btn delete-btn">Delete</a>
                            <a href="../lessons/manage_lessons.php?module_id=<?php echo $module['module_id']; ?>" class="btn">Lessons</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No modules added yet. <a href="add_module.php?course_id=<?php echo $courseId; ?>">Add your first module</a></p>
    <?php endif; ?>
</div>

<?php require_once '../../../includes/footer.php'; ?>
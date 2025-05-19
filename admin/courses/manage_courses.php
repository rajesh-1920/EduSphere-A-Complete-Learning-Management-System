<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['admin']);

$db = new Database();
$courses = $db->query("
    SELECT c.*, u.first_name, u.last_name 
    FROM courses c
    JOIN users u ON c.instructor_id = u.user_id
    ORDER BY c.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Courses';
require_once '../../includes/header.php';
?>

<div class="manage-courses">
    <h1>Manage Courses</h1>
    
    <div class="action-bar">
        <a href="add_course.php" class="btn">Add New Course</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Instructor</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?php echo $course['course_id']; ?></td>
                    <td><?php echo htmlspecialchars($course['title']); ?></td>
                    <td><?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></td>
                    <td><?php echo formatDate($course['created_at']); ?></td>
                    <td>
                        <a href="edit_course.php?id=<?php echo $course['course_id']; ?>" class="btn">Edit</a>
                        <a href="delete_course.php?id=<?php echo $course['course_id']; ?>" class="btn delete-btn">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
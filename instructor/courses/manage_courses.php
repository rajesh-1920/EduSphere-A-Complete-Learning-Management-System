<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$db = new Database();
$courses = $db->query("
    SELECT c.*, COUNT(e.enrollment_id) as students
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.status = 'active'
    WHERE c.instructor_id = $instructorId
    GROUP BY c.course_id
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
    
    <?php if (!empty($courses)): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Students</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['title']); ?></td>
                        <td><?php echo $course['students']; ?></td>
                        <td><?php echo formatDate($course['created_at']); ?></td>
                        <td>
                            <a href="view_course.php?id=<?php echo $course['course_id']; ?>" class="btn">View</a>
                            <a href="edit_course.php?id=<?php echo $course['course_id']; ?>" class="btn">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You don't have any courses yet. <a href="add_course.php">Create your first course</a></p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
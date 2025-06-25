<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Get students enrolled in the instructor's courses
$query = "
    SELECT u.user_id, u.first_name, u.last_name, u.email, 
           COUNT(e.course_id) as enrolled_courses,
           c.title as course_title
    FROM enrollments e
    JOIN users u ON e.student_id = u.user_id
    JOIN courses c ON e.course_id = c.course_id
    WHERE c.instructor_id = $instructorId AND e.status = 'active'
";

if ($courseId > 0) {
    $query .= " AND e.course_id = $courseId";
}

$query .= " GROUP BY u.user_id ORDER BY u.last_name, u.first_name";

$students = $db->query($query)->fetch_all(MYSQLI_ASSOC);

// Get instructor's courses for filter dropdown
$courses = $db->query("
    SELECT course_id, title FROM courses 
    WHERE instructor_id = $instructorId
    ORDER BY title
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Students';
require_once '../../includes/header.php';
?>

<div class="manage-students">
    <h1>Manage Students</h1>
    
    <div class="filter-form">
        <form method="get">
            <select name="course_id" onchange="this.form.submit()">
                <option value="">All Courses</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['course_id']; ?>" <?php echo $courseId == $course['course_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    
    <?php if (!empty($students)): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Enrolled In</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td>
                            <?php if ($courseId > 0): ?>
                                <?php echo htmlspecialchars($student['course_title']); ?>
                            <?php else: ?>
                                <?php echo $student['enrolled_courses']; ?> course(s)
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="view_student.php?id=<?php echo $student['user_id']; ?><?php echo $courseId ? '&course_id='.$courseId : ''; ?>" class="btn">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No students found.</p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
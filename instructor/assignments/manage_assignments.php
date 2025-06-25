<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Get assignments for the instructor's courses
$query = "
    SELECT a.*, c.title as course_title
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    WHERE c.instructor_id = $instructorId
";

if ($courseId > 0) {
    $query .= " AND a.course_id = $courseId";
}

$query .= " ORDER BY a.due_date ASC";

$assignments = $db->query($query)->fetch_all(MYSQLI_ASSOC);

// Get instructor's courses for filter dropdown
$courses = $db->query("
    SELECT course_id, title FROM courses 
    WHERE instructor_id = $instructorId
    ORDER BY title
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Assignments';
require_once '../../includes/header.php';
?>

<div class="manage-assignments">
    <h1>Manage Assignments</h1>
    
    <div class="action-bar">
        <a href="add_assignment.php" class="btn">Add Assignment</a>
        
        <form method="get" class="filter-form">
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
    
    <?php if (!empty($assignments)): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Due Date</th>
                    <th>Submissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['course_title']); ?></td>
                        <td><?php echo formatDate($assignment['due_date']); ?></td>
                        <td>
                            <?php 
                            $submissionCount = $db->query("
                                SELECT COUNT(*) FROM assignment_submissions 
                                WHERE assignment_id = {$assignment['assignment_id']}
                            ")->fetch_row()[0];
                            echo $submissionCount;
                            ?>
                        </td>
                        <td>
                            <a href="view_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn">View</a>
                            <a href="edit_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn">Edit</a>
                            <a href="submissions/view_submissions.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn">Submissions</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No assignments found. <a href="add_assignment.php">Create your first assignment</a></p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
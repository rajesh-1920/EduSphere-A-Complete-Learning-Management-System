<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Get discussions for the instructor's courses
$query = "
    SELECT d.*, c.title as course_title, u.first_name, u.last_name,
           (SELECT COUNT(*) FROM discussion_replies r WHERE r.discussion_id = d.discussion_id) as reply_count
    FROM discussions d
    JOIN courses c ON d.course_id = c.course_id
    JOIN users u ON d.author_id = u.user_id
    WHERE c.instructor_id = $instructorId
";

if ($courseId > 0) {
    $query .= " AND d.course_id = $courseId";
}

$query .= " ORDER BY d.created_at DESC";

$discussions = $db->query($query)->fetch_all(MYSQLI_ASSOC);

// Get instructor's courses for filter dropdown
$courses = $db->query("
    SELECT course_id, title FROM courses 
    WHERE instructor_id = $instructorId
    ORDER BY title
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Discussions';
require_once '../../includes/header.php';
?>

<div class="manage-discussions">
    <h1>Manage Discussions</h1>
    
    <div class="action-bar">
        <a href="add_discussion.php" class="btn">Add Discussion</a>
        
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
    
    <?php if (!empty($discussions)): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Author</th>
                    <th>Replies</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($discussions as $discussion): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($discussion['title']); ?></td>
                        <td><?php echo htmlspecialchars($discussion['course_title']); ?></td>
                        <td><?php echo htmlspecialchars($discussion['first_name'] . ' ' . $discussion['last_name']); ?></td>
                        <td><?php echo $discussion['reply_count']; ?></td>
                        <td><?php echo formatDate($discussion['created_at']); ?></td>
                        <td>
                            <a href="view_discussion.php?id=<?php echo $discussion['discussion_id']; ?>" class="btn">View</a>
                            <a href="delete_discussion.php?id=<?php echo $discussion['discussion_id']; ?>" class="btn delete-btn">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No discussions found. <a href="add_discussion.php">Start your first discussion</a></p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
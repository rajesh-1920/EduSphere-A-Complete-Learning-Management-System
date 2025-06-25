<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

$studentId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$db = new Database();

// Get discussions
$query = "
    SELECT d.*, c.title as course_title, u.first_name, u.last_name,
           (SELECT COUNT(*) FROM discussion_replies r WHERE r.discussion_id = d.discussion_id) as reply_count
    FROM discussions d
    JOIN courses c ON d.course_id = c.course_id
    JOIN users u ON d.author_id = u.user_id
    JOIN enrollments e ON d.course_id = c.course_id
    WHERE e.student_id = $studentId AND e.status = 'active'
";

if ($courseId > 0) {
    $query .= " AND d.course_id = $courseId";
}

$query .= " ORDER BY d.created_at DESC";

$discussions = $db->query($query)->fetch_all(MYSQLI_ASSOC);

// Get enrolled courses for filter
$courses = $db->query("
    SELECT c.course_id, c.title
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.student_id = $studentId AND e.status = 'active'
    ORDER BY c.title
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Discussions';
require_once '../../includes/header.php';
?>

<div class="discussions-list">
    <h1>Discussions</h1>
    
    <form method="get" class="filter-form mb-4">
        <div class="row">
            <div class="col-md-6">
                <label for="course_id" class="form-label">Filter by Course:</label>
                <select name="course_id" id="course_id" class="form-select">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['course_id']; ?>" 
                            <?php echo $course['course_id'] == $courseId ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>
    
    <?php if (!empty($discussions)): ?>
        <div class="discussions-container">
            <?php foreach ($discussions as $discussion): ?>
                <div class="discussion-card">
                    <div class="discussion-header">
                        <h3>
                            <a href="view_discussion.php?id=<?php echo $discussion['discussion_id']; ?>">
                                <?php echo htmlspecialchars($discussion['title']); ?>
                            </a>
                        </h3>
                        <div class="discussion-meta">
                            <span class="course"><?php echo htmlspecialchars($discussion['course_title']); ?></span>
                            <span class="author">Started by <?php echo htmlspecialchars($discussion['first_name'] . ' ' . $discussion['last_name']); ?></span>
                            <span class="date"><?php echo formatDate($discussion['created_at']); ?></span>
                            <span class="replies"><?php echo $discussion['reply_count']; ?> replies</span>
                        </div>
                    </div>
                    <div class="discussion-content">
                        <?php echo nl2br(htmlspecialchars(substr($discussion['content'], 0, 200))); ?>...
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            No discussions found.
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
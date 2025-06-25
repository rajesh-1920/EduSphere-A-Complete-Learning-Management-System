<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

$studentId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$db = new Database();

// Get announcements
$query = "
    SELECT a.*, c.title as course_title, u.first_name, u.last_name
    FROM announcements a
    JOIN courses c ON a.course_id = c.course_id
    JOIN users u ON a.author_id = u.user_id
    JOIN enrollments e ON a.course_id = e.course_id
    WHERE e.student_id = $studentId AND e.status = 'active'
";

if ($courseId > 0) {
    $query .= " AND a.course_id = $courseId";
}

$query .= " ORDER BY a.created_at DESC";

$announcements = $db->query($query)->fetch_all(MYSQLI_ASSOC);

// Get enrolled courses for filter
$courses = $db->query("
    SELECT c.course_id, c.title
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.student_id = $studentId AND e.status = 'active'
    ORDER BY c.title
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Announcements';
require_once '../../includes/header.php';
?>

<div class="announcements-list">
    <h1>Announcements</h1>
    
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
    
    <?php if (!empty($announcements)): ?>
        <div class="announcements-container">
            <?php foreach ($announcements as $announcement): ?>
                <div class="announcement-card">
                    <div class="announcement-header">
                        <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                        <div class="announcement-meta">
                            <span class="course"><?php echo htmlspecialchars($announcement['course_title']); ?></span>
                            <span class="author">Posted by <?php echo htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']); ?></span>
                            <span class="date"><?php echo formatDate($announcement['created_at']); ?></span>
                        </div>
                    </div>
                    <div class="announcement-content">
                        <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                    </div>
                    <div class="announcement-actions">
                        <a href="view_announcement.php?id=<?php echo $announcement['announcement_id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            No announcements found.
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
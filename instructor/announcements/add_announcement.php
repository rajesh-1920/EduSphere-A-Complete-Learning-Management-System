<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$errors = [];

// Get courses taught by this instructor
$db = new Database();
$courses = $db->query("
    SELECT course_id, title 
    FROM courses 
    WHERE instructor_id = $instructorId
    ORDER BY title
")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = sanitize($_POST['course_id']);
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);

    // Validate inputs
    if (empty($courseId)) $errors[] = 'Course is required';
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($content)) $errors[] = 'Content is required';

    if (empty($errors)) {
        // Insert the announcement
        $stmt = $db->prepare("INSERT INTO announcements (course_id, author_id, title, content) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $courseId, $instructorId, $title, $content);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Announcement added successfully!';
            redirect('manage_announcements.php');
        } else {
            $errors[] = 'Failed to add announcement. Please try again.';
        }
    }
}

$pageTitle = 'Add Announcement';
require_once '../../includes/header.php';
?>

<div class="add-announcement-form">
    <h1>Add New Announcement</h1>

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
            <label for="course_id">Course</label>
            <select id="course_id" name="course_id" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['course_id']; ?>">
                        <?php echo htmlspecialchars($course['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="6" required></textarea>
        </div>
        <button type="submit" class="btn">Add Announcement</button>
        <a href="manage_announcements.php" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['admin']);

if (!isset($_GET['id'])) {
    redirect('manage_courses.php');
}

$courseId = $_GET['id'];
$db = new Database();

// Get course data
$course = $db->query("SELECT * FROM courses WHERE course_id = $courseId")->fetch_assoc();
if (!$course) {
    redirect('manage_courses.php');
}

// Get all instructors for dropdown
$instructors = $db->query("SELECT user_id, first_name, last_name FROM users WHERE role = 'instructor'")->fetch_all(MYSQLI_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $instructorId = sanitize($_POST['instructor_id']);

    // Validate inputs
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($instructorId)) $errors[] = 'Instructor is required';

    // Handle thumbnail upload
    $thumbnail = $course['thumbnail'];
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['thumbnail'], COURSE_THUMBNAIL_PATH);
        if ($uploadResult['success']) {
            // Delete old thumbnail if it exists
            if ($thumbnail) {
                @unlink(COURSE_THUMBNAIL_PATH . $thumbnail);
            }
            $thumbnail = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['message'];
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE courses SET title = ?, description = ?, thumbnail = ?, instructor_id = ? WHERE course_id = ?");
        $stmt->bind_param("sssii", $title, $description, $thumbnail, $instructorId, $courseId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Course updated successfully!';
            redirect('manage_courses.php');
        } else {
            $errors[] = 'Failed to update course. Please try again.';
        }
    }
}

$pageTitle = 'Edit Course';
require_once '../../includes/header.php';
?>

<div class="edit-course-form">
    <h1>Edit Course: <?php echo htmlspecialchars($course['title']); ?></h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Course Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($course['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="instructor_id">Instructor</label>
            <select id="instructor_id" name="instructor_id" required>
                <option value="">Select Instructor</option>
                <?php foreach ($instructors as $instructor): ?>
                    <option value="<?php echo $instructor['user_id']; ?>" <?php echo $instructor['user_id'] == $course['instructor_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="thumbnail">Thumbnail Image</label>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
            <?php if ($course['thumbnail']): ?>
                <div class="current-image">
                    <p>Current Thumbnail:</p>
                    <img src="<?php echo SITE_URL; ?>/uploads/course_thumbnails/<?php echo $course['thumbnail']; ?>" alt="Course Thumbnail" width="150">
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn">Update Course</button>
        <a href="manage_courses.php" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
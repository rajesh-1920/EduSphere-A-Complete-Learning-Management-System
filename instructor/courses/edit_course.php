<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('manage_courses.php');
}

$courseId = $_GET['id'];
$instructorId = $_SESSION['user_id'];
$db = new Database();

// Get course data
$course = $db->query("SELECT * FROM courses WHERE course_id = $courseId AND instructor_id = $instructorId")->fetch_assoc();
if (!$course) {
    redirect('manage_courses.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);

    // Validate inputs
    if (empty($title)) $errors[] = 'Title is required';

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
        $stmt = $db->prepare("UPDATE courses SET title = ?, description = ?, thumbnail = ? WHERE course_id = ?");
        $stmt->bind_param("sssi", $title, $description, $thumbnail, $courseId);
        
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
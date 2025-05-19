<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['admin']);

$errors = [];

// Get all instructors for dropdown
$db = new Database();
$instructors = $db->query("SELECT user_id, first_name, last_name FROM users WHERE role = 'instructor'")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $instructorId = sanitize($_POST['instructor_id']);

    // Validate inputs
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($instructorId)) $errors[] = 'Instructor is required';

    // Handle thumbnail upload
    $thumbnail = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['thumbnail'], COURSE_THUMBNAIL_PATH);
        if ($uploadResult['success']) {
            $thumbnail = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['message'];
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO courses (title, description, thumbnail, instructor_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $description, $thumbnail, $instructorId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Course added successfully!';
            redirect('manage_courses.php');
        } else {
            $errors[] = 'Failed to add course. Please try again.';
        }
    }
}

$pageTitle = 'Add New Course';
require_once '../../includes/header.php';
?>

<div class="add-course-form">
    <h1>Add New Course</h1>
    
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
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        <div class="form-group">
            <label for="instructor_id">Instructor</label>
            <select id="instructor_id" name="instructor_id" required>
                <option value="">Select Instructor</option>
                <?php foreach ($instructors as $instructor): ?>
                    <option value="<?php echo $instructor['user_id']; ?>">
                        <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="thumbnail">Thumbnail Image</label>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
        </div>
        <button type="submit" class="btn">Add Course</button>
        <a href="manage_courses.php" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
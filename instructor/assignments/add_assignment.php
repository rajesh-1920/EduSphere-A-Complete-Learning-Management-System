<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Get instructor's courses
$courses = $db->query("
    SELECT course_id, title FROM courses 
    WHERE instructor_id = $instructorId
    ORDER BY title
")->fetch_all(MYSQLI_ASSOC);

if (empty($courses)) {
    $_SESSION['error_message'] = 'You need to create a course first before adding assignments.';
    redirect('../courses/manage_courses.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $courseId = (int)$_POST['course_id'];
    $dueDate = sanitize($_POST['due_date']);
    $maxPoints = (int)$_POST['max_points'];

    // Validate inputs
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($courseId)) $errors[] = 'Course is required';
    if (empty($dueDate)) $errors[] = 'Due date is required';
    if ($maxPoints <= 0) $errors[] = 'Max points must be a positive number';

    // Verify instructor owns the course
    $validCourse = false;
    foreach ($courses as $course) {
        if ($course['course_id'] == $courseId) {
            $validCourse = true;
            break;
        }
    }

    if (!$validCourse) {
        $errors[] = 'Invalid course selected';
    }

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO assignments (course_id, title, description, due_date, max_points) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $courseId, $title, $description, $dueDate, $maxPoints);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Assignment added successfully!';
            redirect('manage_assignments.php');
        } else {
            $errors[] = 'Failed to add assignment. Please try again.';
        }
    }
}

$pageTitle = 'Add Assignment';
require_once '../../includes/header.php';
?>

<div class="add-assignment-form">
    <h1>Add Assignment</h1>
    
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
            <label for="title">Assignment Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="6" class="rich-text"></textarea>
        </div>
        <div class="form-group">
            <label for="course_id">Course</label>
            <select id="course_id" name="course_id" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['course_id']; ?>" <?php echo $courseId == $course['course_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="datetime-local" id="due_date" name="due_date" required>
        </div>
        <div class="form-group">
            <label for="max_points">Max Points</label>
            <input type="number" id="max_points" name="max_points" min="1" value="100" required>
        </div>
        <button type="submit" class="btn">Add Assignment</button>
        <a href="manage_assignments.php" class="btn">Cancel</a>
    </form>
</div>

<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '.rich-text',
    plugins: 'advlist link image lists code',
    toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code',
    height: 300
});

// Set default due date to tomorrow at 11:59 PM
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
tomorrow.setHours(23, 59, 0, 0);

document.getElementById('due_date').value = tomorrow.toISOString().slice(0, 16);
</script>

<?php require_once '../../includes/footer.php'; ?>
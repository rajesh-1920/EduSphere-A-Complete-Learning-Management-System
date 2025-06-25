<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('manage_assignments.php');
}

$assignmentId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get assignment data and verify ownership
$assignment = $db->query("
    SELECT a.*, c.title as course_title 
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    WHERE a.assignment_id = $assignmentId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$assignment) {
    redirect('manage_assignments.php');
}

// Get instructor's courses
$courses = $db->query("
    SELECT course_id, title FROM courses 
    WHERE instructor_id = $instructorId
    ORDER BY title
")->fetch_all(MYSQLI_ASSOC);

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
        $stmt = $db->prepare("UPDATE assignments SET course_id = ?, title = ?, description = ?, due_date = ?, max_points = ? WHERE assignment_id = ?");
        $stmt->bind_param("isssii", $courseId, $title, $description, $dueDate, $maxPoints, $assignmentId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Assignment updated successfully!';
            redirect('manage_assignments.php');
        } else {
            $errors[] = 'Failed to update assignment. Please try again.';
        }
    }
}

$pageTitle = 'Edit Assignment';
require_once '../../includes/header.php';
?>

<div class="edit-assignment-form">
    <h1>Edit Assignment</h1>
    <p>Course: <?php echo htmlspecialchars($assignment['course_title']); ?></p>
    
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
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($assignment['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="6" class="rich-text"><?php echo htmlspecialchars($assignment['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="course_id">Course</label>
            <select id="course_id" name="course_id" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['course_id']; ?>" <?php echo $course['course_id'] == $assignment['course_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="datetime-local" id="due_date" name="due_date" value="<?php echo date('Y-m-d\TH:i', strtotime($assignment['due_date'])); ?>" required>
        </div>
        <div class="form-group">
            <label for="max_points">Max Points</label>
            <input type="number" id="max_points" name="max_points" min="1" value="<?php echo $assignment['max_points']; ?>" required>
        </div>
        <button type="submit" class="btn">Update Assignment</button>
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
</script>

<?php require_once '../../includes/footer.php'; ?>
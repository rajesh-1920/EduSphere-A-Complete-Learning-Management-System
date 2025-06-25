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
    $_SESSION['error_message'] = 'You need to create a course first before adding quizzes.';
    redirect('../courses/manage_courses.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $courseId = (int)$_POST['course_id'];
    $timeLimit = (int)$_POST['time_limit'];
    $availableFrom = sanitize($_POST['available_from']);
    $availableTo = sanitize($_POST['available_to']);

    // Validate inputs
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($courseId)) $errors[] = 'Course is required';
    if ($timeLimit < 0) $errors[] = 'Time limit must be a positive number';
    if (empty($availableFrom)) $errors[] = 'Available from date is required';
    if (empty($availableTo)) $errors[] = 'Available to date is required';

    if (strtotime($availableFrom) > strtotime($availableTo)) {
        $errors[] = 'Available to date must be after available from date';
    }

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
        $stmt = $db->prepare("INSERT INTO quizzes (course_id, title, description, time_limit, available_from, available_to) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississ", $courseId, $title, $description, $timeLimit, $availableFrom, $availableTo);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Quiz added successfully!';
            redirect('manage_quizzes.php');
        } else {
            $errors[] = 'Failed to add quiz. Please try again.';
        }
    }
}

$pageTitle = 'Add Quiz';
require_once '../../includes/header.php';
?>

<div class="add-quiz-form">
    <h1>Add Quiz</h1>
    
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
            <label for="title">Quiz Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
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
            <label for="time_limit">Time Limit (minutes, 0 for no limit)</label>
            <input type="number" id="time_limit" name="time_limit" min="0" value="30">
        </div>
        <div class="form-group">
            <label for="available_from">Available From</label>
            <input type="datetime-local" id="available_from" name="available_from" required>
        </div>
        <div class="form-group">
            <label for="available_to">Available To</label>
            <input type="datetime-local" id="available_to" name="available_to" required>
        </div>
        <button type="submit" class="btn">Add Quiz</button>
        <a href="manage_quizzes.php" class="btn">Cancel</a>
    </form>
</div>

<script>
// Set default available dates
const now = new Date();
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
const nextWeek = new Date();
nextWeek.setDate(nextWeek.getDate() + 7);

document.getElementById('available_from').value = tomorrow.toISOString().slice(0, 16);
document.getElementById('available_to').value = nextWeek.toISOString().slice(0, 16);
</script>

<?php require_once '../../includes/footer.php'; ?>
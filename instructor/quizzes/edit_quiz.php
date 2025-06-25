<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('manage_quizzes.php');
}

$quizId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get quiz data and verify ownership
$quiz = $db->query("
    SELECT q.*, c.title as course_title 
    FROM quizzes q
    JOIN courses c ON q.course_id = c.course_id
    WHERE q.quiz_id = $quizId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$quiz) {
    redirect('manage_quizzes.php');
}

// Get instructor's courses
$courses = $db->query("
    SELECT course_id, title FROM courses 
    WHERE instructor_id = $instructorId
    ORDER by title
")->fetch_all(MYSQLI_ASSOC);

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
        $stmt = $db->prepare("UPDATE quizzes SET course_id = ?, title = ?, description = ?, time_limit = ?, available_from = ?, available_to = ? WHERE quiz_id = ?");
        $stmt->bind_param("ississi", $courseId, $title, $description, $timeLimit, $availableFrom, $availableTo, $quizId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Quiz updated successfully!';
            redirect('manage_quizzes.php');
        } else {
            $errors[] = 'Failed to update quiz. Please try again.';
        }
    }
}

$pageTitle = 'Edit Quiz';
require_once '../../includes/header.php';
?>

<div class="edit-quiz-form">
    <h1>Edit Quiz</h1>
    <p>Course: <?php echo htmlspecialchars($quiz['course_title']); ?></p>
    
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
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($quiz['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="course_id">Course</label>
            <select id="course_id" name="course_id" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['course_id']; ?>" <?php echo $course['course_id'] == $quiz['course_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="time_limit">Time Limit (minutes, 0 for no limit)</label>
            <input type="number" id="time_limit" name="time_limit" min="0" value="<?php echo $quiz['time_limit']; ?>">
        </div>
        <div class="form-group">
            <label for="available_from">Available From</label>
            <input type="datetime-local" id="available_from" name="available_from" value="<?php echo date('Y-m-d\TH:i', strtotime($quiz['available_from'])); ?>" required>
        </div>
        <div class="form-group">
            <label for="available_to">Available To</label>
            <input type="datetime-local" id="available_to" name="available_to" value="<?php echo date('Y-m-d\TH:i', strtotime($quiz['available_to'])); ?>" required>
        </div>
        <button type="submit" class="btn">Update Quiz</button>
        <a href="manage_quizzes.php" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
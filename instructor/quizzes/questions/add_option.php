<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('../../manage_quizzes.php');
}

$questionId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get question data and verify ownership
$question = $db->query("
    SELECT q.*, c.title as course_title, qz.title as quiz_title
    FROM quiz_questions q
    JOIN quizzes qz ON q.quiz_id = qz.quiz_id
    JOIN courses c ON qz.course_id = c.course_id
    WHERE q.question_id = $questionId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$question || $question['question_type'] === 'short_answer') {
    redirect('../../manage_quizzes.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $optionText = sanitize($_POST['option_text']);
    $isCorrect = isset($_POST['is_correct']) ? 1 : 0;

    // Validate inputs
    if (empty($optionText)) $errors[] = 'Option text is required';

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO quiz_question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $questionId, $optionText, $isCorrect);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Option added successfully!';
            redirect("manage_options.php?id=$questionId");
        } else {
            $errors[] = 'Failed to add option. Please try again.';
        }
    }
}

$pageTitle = 'Add Option';
require_once '../../../includes/header.php';
?>

<div class="add-option-form">
    <h1>Add Option</h1>
    <p>Quiz: <?php echo htmlspecialchars($question['quiz_title']); ?></p>
    <p>Course: <?php echo htmlspecialchars($question['course_title']); ?></p>
    <p>Question: <?php echo htmlspecialchars($question['question_text']); ?></p>
    
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
            <label for="option_text">Option Text</label>
            <input type="text" id="option_text" name="option_text" required>
        </div>
        <div class="form-group">
            <label for="is_correct">
                <input type="checkbox" id="is_correct" name="is_correct">
                Correct Answer
            </label>
        </div>
        <button type="submit" class="btn">Add Option</button>
        <a href="manage_options.php?id=<?php echo $questionId; ?>" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../../includes/footer.php'; ?>
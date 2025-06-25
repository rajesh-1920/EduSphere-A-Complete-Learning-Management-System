<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('../../manage_quizzes.php');
}

$optionId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get option data and verify ownership
$option = $db->query("
    SELECT o.*, q.question_text, q.question_type, qz.title as quiz_title, c.title as course_title
    FROM quiz_question_options o
    JOIN quiz_questions q ON o.question_id = q.question_id
    JOIN quizzes qz ON q.quiz_id = qz.quiz_id
    JOIN courses c ON qz.course_id = c.course_id
    WHERE o.option_id = $optionId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$option || $option['question_type'] === 'short_answer') {
    redirect('../../manage_quizzes.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $optionText = sanitize($_POST['option_text']);
    $isCorrect = isset($_POST['is_correct']) ? 1 : 0;

    // Validate inputs
    if (empty($optionText)) $errors[] = 'Option text is required';

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE quiz_question_options SET option_text = ?, is_correct = ? WHERE option_id = ?");
        $stmt->bind_param("sii", $optionText, $isCorrect, $optionId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Option updated successfully!';
            redirect("manage_options.php?id={$option['question_id']}");
        } else {
            $errors[] = 'Failed to update option. Please try again.';
        }
    }
}

$pageTitle = 'Edit Option';
require_once '../../../includes/header.php';
?>

<div class="edit-option-form">
    <h1>Edit Option</h1>
    <p>Quiz: <?php echo htmlspecialchars($option['quiz_title']); ?></p>
    <p>Course: <?php echo htmlspecialchars($option['course_title']); ?></p>
    <p>Question: <?php echo htmlspecialchars($option['question_text']); ?></p>
    
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
            <input type="text" id="option_text" name="option_text" value="<?php echo htmlspecialchars($option['option_text']); ?>" required>
        </div>
        <div class="form-group">
            <label for="is_correct">
                <input type="checkbox" id="is_correct" name="is_correct" <?php echo $option['is_correct'] ? 'checked' : ''; ?>>
                Correct Answer
            </label>
        </div>
        <button type="submit" class="btn">Update Option</button>
        <a href="manage_options.php?id=<?php echo $option['question_id']; ?>" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../../includes/footer.php'; ?>
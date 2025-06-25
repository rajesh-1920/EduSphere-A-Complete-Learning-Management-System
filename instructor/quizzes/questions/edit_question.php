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

if (!$question) {
    redirect('../../manage_quizzes.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionText = sanitize($_POST['question_text']);
    $points = (int)$_POST['points'];
    $position = (int)$_POST['position'];

    // Validate inputs
    if (empty($questionText)) $errors[] = 'Question text is required';
    if ($points <= 0) $errors[] = 'Points must be a positive number';
    if ($position <= 0) $errors[] = 'Position must be a positive number';

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE quiz_questions SET question_text = ?, points = ?, position = ? WHERE question_id = ?");
        $stmt->bind_param("siii", $questionText, $points, $position, $questionId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Question updated successfully!';
            redirect("manage_questions.php?id={$question['quiz_id']}");
        } else {
            $errors[] = 'Failed to update question. Please try again.';
        }
    }
}

$pageTitle = 'Edit Question';
require_once '../../../includes/header.php';
?>

<div class="edit-question-form">
    <h1>Edit Question</h1>
    <p>Quiz: <?php echo htmlspecialchars($question['quiz_title']); ?></p>
    <p>Course: <?php echo htmlspecialchars($question['course_title']); ?></p>
    <p>Type: <?php 
        switch($question['question_type']) {
            case 'multiple_choice': echo 'Multiple Choice'; break;
            case 'true_false': echo 'True/False'; break;
            case 'short_answer': echo 'Short Answer'; break;
            default: echo $question['question_type'];
        }
    ?></p>
    
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
            <label for="question_text">Question Text</label>
            <textarea id="question_text" name="question_text" rows="3" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="points">Points</label>
            <input type="number" id="points" name="points" min="1" value="<?php echo $question['points']; ?>" required>
        </div>
        <div class="form-group">
            <label for="position">Position</label>
            <input type="number" id="position" name="position" min="1" value="<?php echo $question['position']; ?>" required>
        </div>
        <button type="submit" class="btn">Update Question</button>
        <a href="manage_questions.php?id=<?php echo $question['quiz_id']; ?>" class="btn">Cancel</a>
    </form>
    
    <?php if ($question['question_type'] !== 'short_answer'): ?>
        <div class="question-options">
            <h3>Options</h3>
            <a href="manage_options.php?id=<?php echo $questionId; ?>" class="btn">Manage Options</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../../includes/footer.php'; ?>
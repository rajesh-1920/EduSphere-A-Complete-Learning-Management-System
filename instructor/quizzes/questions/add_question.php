<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('../../manage_quizzes.php');
}

$quizId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Verify instructor owns this quiz
$quiz = $db->query("
    SELECT q.*, c.title as course_title 
    FROM quizzes q
    JOIN courses c ON q.course_id = c.course_id
    WHERE q.quiz_id = $quizId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$quiz) {
    redirect('../../manage_quizzes.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionText = sanitize($_POST['question_text']);
    $questionType = sanitize($_POST['question_type']);
    $points = (int)$_POST['points'];
    $position = (int)$_POST['position'];

    // Validate inputs
    if (empty($questionText)) $errors[] = 'Question text is required';
    if (!in_array($questionType, ['multiple_choice', 'true_false', 'short_answer'])) $errors[] = 'Invalid question type';
    if ($points <= 0) $errors[] = 'Points must be a positive number';
    if ($position <= 0) $errors[] = 'Position must be a positive number';

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO quiz_questions (quiz_id, question_text, question_type, points, position) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issii", $quizId, $questionText, $questionType, $points, $position);
        
        if ($stmt->execute()) {
            $questionId = $db->getLastInsertId();
            
            // For True/False questions, add default options
            if ($questionType === 'true_false') {
                $db->query("INSERT INTO quiz_question_options (question_id, option_text, is_correct) VALUES ($questionId, 'True', 1)");
                $db->query("INSERT INTO quiz_question_options (question_id, option_text, is_correct) VALUES ($questionId, 'False', 0)");
            }
            
            $_SESSION['success_message'] = 'Question added successfully!';
            
            // Redirect based on question type
            if ($questionType === 'short_answer') {
                redirect("manage_questions.php?id=$quizId");
            } else {
                redirect("manage_options.php?id=$questionId");
            }
        } else {
            $errors[] = 'Failed to add question. Please try again.';
        }
    }
}

// Get next available position
$nextPosition = $db->query("SELECT IFNULL(MAX(position), 0) + 1 FROM quiz_questions WHERE quiz_id = $quizId")->fetch_row()[0];

$pageTitle = 'Add Question';
require_once '../../../includes/header.php';
?>

<div class="add-question-form">
    <h1>Add Question</h1>
    <p>Quiz: <?php echo htmlspecialchars($quiz['title']); ?></p>
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
            <label for="question_text">Question Text</label>
            <textarea id="question_text" name="question_text" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="question_type">Question Type</label>
            <select id="question_type" name="question_type" required onchange="toggleOptionsField()">
                <option value="">Select Type</option>
                <option value="multiple_choice">Multiple Choice</option>
                <option value="true_false">True/False</option>
                <option value="short_answer">Short Answer</option>
            </select>
        </div>
        <div class="form-group">
            <label for="points">Points</label>
            <input type="number" id="points" name="points" min="1" value="1" required>
        </div>
        <div class="form-group">
            <label for="position">Position</label>
            <input type="number" id="position" name="position" min="1" value="<?php echo $nextPosition; ?>" required>
        </div>
        <button type="submit" class="btn">Add Question</button>
        <a href="manage_questions.php?id=<?php echo $quizId; ?>" class="btn">Cancel</a>
    </form>
</div>

<script>
function toggleOptionsField() {
    // This function would be used to show/hide options fields based on question type
    // Implementation would depend on your specific UI needs
}
</script>

<?php require_once '../../../includes/footer.php'; ?>
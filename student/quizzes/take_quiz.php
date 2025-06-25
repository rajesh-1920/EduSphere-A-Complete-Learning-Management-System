<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

// Check if we have a quiz ID or attempt ID
if (!isset($_GET['id']) && !isset($_GET['attempt_id'])) {
    redirect('my_quizzes.php');
}

$studentId = $_SESSION['user_id'];
$db = new Database();

// Handle quiz attempt
if (isset($_GET['attempt_id'])) {
    $attemptId = (int)$_GET['attempt_id'];
    
    // Get existing attempt
    $attempt = $db->query("
        SELECT a.*, q.title, q.time_limit, q.available_to
        FROM quiz_attempts a
        JOIN quizzes q ON a.quiz_id = q.quiz_id
        WHERE a.attempt_id = $attemptId
        AND a.student_id = $studentId
        AND a.completed_at IS NULL
    ")->fetch_assoc();
    
    if (!$attempt) {
        $_SESSION['error'] = "Invalid attempt or already completed";
        redirect('my_quizzes.php');
    }
    
    $quizId = $attempt['quiz_id'];
} else {
    $quizId = (int)$_GET['id'];
    
    // Verify quiz is available
    $quiz = $db->query("
        SELECT q.*, c.title as course_title
        FROM quizzes q
        JOIN courses c ON q.course_id = c.course_id
        JOIN enrollments e ON q.course_id = c.course_id
        WHERE q.quiz_id = $quizId
        AND e.student_id = $studentId
        AND e.status = 'active'
        AND (q.available_from IS NULL OR q.available_from <= NOW())
        AND (q.available_to IS NULL OR q.available_to >= NOW())
    ")->fetch_assoc();
    
    if (!$quiz) {
        $_SESSION['error'] = "Quiz not available";
        redirect('my_quizzes.php');
    }
    
    // Create new attempt
    $db->query("
        INSERT INTO quiz_attempts (quiz_id, student_id, started_at)
        VALUES ($quizId, $studentId, NOW())
    ");
    
    $attemptId = $db->getLastInsertId();
    $attempt = [
        'attempt_id' => $attemptId,
        'quiz_id' => $quizId,
        'title' => $quiz['title'],
        'time_limit' => $quiz['time_limit'],
        'available_to' => $quiz['available_to']
    ];
}

// Get quiz questions
$questions = $db->query("
    SELECT q.* 
    FROM quiz_questions q
    WHERE q.quiz_id = $quizId
    ORDER BY q.position ASC
")->fetch_all(MYSQLI_ASSOC);

// Get question options
foreach ($questions as &$question) {
    $question['options'] = $db->query("
        SELECT * FROM quiz_question_options
        WHERE question_id = {$question['question_id']}
        ORDER BY option_id
    ")->fetch_all(MYSQLI_ASSOC);
}
unset($question);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save answers
    foreach ($_POST['answers'] as $questionId => $answer) {
        $questionId = (int)$questionId;
        $answerText = is_array($answer) ? implode(',', $answer) : $answer;
        
        // Check if answer already exists
        $exists = $db->query("
            SELECT answer_id FROM quiz_answers
            WHERE attempt_id = $attemptId
            AND question_id = $questionId
        ")->fetch_row();
        
        if ($exists) {
            $stmt = $db->prepare("
                UPDATE quiz_answers SET answer_text = ?
                WHERE answer_id = ?
            ");
            $stmt->bind_param("si", $answerText, $exists[0]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO quiz_answers (attempt_id, question_id, answer_text)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iis", $attemptId, $questionId, $answerText);
        }
        $stmt->execute();
    }
    
    // Check if quiz is being submitted
    if (isset($_POST['submit_quiz'])) {
        // Grade the quiz
        $db->query("
            UPDATE quiz_attempts 
            SET completed_at = NOW() 
            WHERE attempt_id = $attemptId
        ");
        
        $_SESSION['success'] = "Quiz submitted successfully!";
        redirect("quiz_results.php?attempt_id=$attemptId");
    } else {
        $_SESSION['success'] = "Progress saved";
        redirect("take_quiz.php?attempt_id=$attemptId");
    }
}

// Calculate time remaining
$timeRemaining = null;
if ($attempt['time_limit']) {
    $startTime = strtotime($db->query("
        SELECT started_at FROM quiz_attempts 
        WHERE attempt_id = $attemptId
    ")->fetch_row()[0]);
    
    $endTime = $startTime + ($attempt['time_limit'] * 60);
    $timeRemaining = $endTime - time();
    
    if ($timeRemaining <= 0) {
        // Time's up - submit the quiz
        $db->query("
            UPDATE quiz_attempts 
            SET completed_at = NOW() 
            WHERE attempt_id = $attemptId
        ");
        $_SESSION['error'] = "Time's up! Quiz auto-submitted.";
        redirect("quiz_results.php?attempt_id=$attemptId");
    }
}

$pageTitle = "Take Quiz: " . htmlspecialchars($attempt['title']);
require_once '../../includes/header.php';
?>

<div class="take-quiz">
    <h1><?php echo htmlspecialchars($attempt['title']); ?></h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if ($timeRemaining !== null): ?>
        <div class="quiz-timer">
            Time remaining: <span id="time-remaining"><?php echo gmdate("i:s", $timeRemaining); ?></span>
        </div>
    <?php endif; ?>
    
    <form method="post" id="quiz-form">
        <?php foreach ($questions as $index => $question): ?>
            <div class="quiz-question">
                <h3>Question <?php echo $index + 1; ?></h3>
                <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                
                <?php if ($question['question_type'] === 'multiple_choice'): ?>
                    <div class="question-options">
                        <?php foreach ($question['options'] as $option): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" 
                                       name="answers[<?php echo $question['question_id']; ?>]" 
                                       id="option_<?php echo $option['option_id']; ?>"
                                       value="<?php echo $option['option_id']; ?>">
                                <label class="form-check-label" for="option_<?php echo $option['option_id']; ?>">
                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                
                <?php elseif ($question['question_type'] === 'true_false'): ?>
                    <div class="question-options">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" 
                                   name="answers[<?php echo $question['question_id']; ?>]" 
                                   id="true_<?php echo $question['question_id']; ?>"
                                   value="true">
                            <label class="form-check-label" for="true_<?php echo $question['question_id']; ?>">
                                True
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" 
                                   name="answers[<?php echo $question['question_id']; ?>]" 
                                   id="false_<?php echo $question['question_id']; ?>"
                                   value="false">
                            <label class="form-check-label" for="false_<?php echo $question['question_id']; ?>">
                                False
                            </label>
                        </div>
                    </div>
                
                <?php else: // short_answer ?>
                    <div class="form-group">
                        <textarea class="form-control" 
                                  name="answers[<?php echo $question['question_id']; ?>]"
                                  rows="3"></textarea>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="quiz-actions">
            <button type="submit" name="save_progress" class="btn btn-secondary">Save Progress</button>
            <button type="submit" name="submit_quiz" class="btn btn-primary">Submit Quiz</button>
        </div>
    </form>
</div>

<?php if ($timeRemaining !== null): ?>
<script>
// Timer countdown
const timeRemaining = <?php echo $timeRemaining; ?>;
let secondsLeft = timeRemaining;

function updateTimer() {
    const minutes = Math.floor(secondsLeft / 60);
    const seconds = secondsLeft % 60;
    document.getElementById('time-remaining').textContent = 
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    if (secondsLeft <= 0) {
        document.getElementById('quiz-form').submit();
    } else {
        secondsLeft--;
        setTimeout(updateTimer, 1000);
    }
}

// Start timer
updateTimer();
</script>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>
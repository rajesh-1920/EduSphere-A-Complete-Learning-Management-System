<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

if (!isset($_GET['attempt_id'])) {
    redirect('my_quizzes.php');
}

$attemptId = (int)$_GET['attempt_id'];
$studentId = $_SESSION['user_id'];
$db = new Database();

// Get attempt details
$attempt = $db->query("
    SELECT a.*, q.title as quiz_title, q.course_id, c.title as course_title
    FROM quiz_attempts a
    JOIN quizzes q ON a.quiz_id = q.quiz_id
    JOIN courses c ON q.course_id = c.course_id
    WHERE a.attempt_id = $attemptId
    AND a.student_id = $studentId
")->fetch_assoc();

if (!$attempt) {
    redirect('my_quizzes.php');
}

// Get questions and answers
$questions = $db->query("
    SELECT q.*, a.answer_text, a.points_earned, a.is_correct
    FROM quiz_questions q
    LEFT JOIN quiz_answers a ON q.question_id = a.question_id AND a.attempt_id = $attemptId
    WHERE q.quiz_id = {$attempt['quiz_id']}
    ORDER BY q.position ASC
")->fetch_all(MYSQLI_ASSOC);

// Calculate total points
$totalPoints = 0;
$pointsEarned = 0;

foreach ($questions as $question) {
    $totalPoints += $question['points'];
    if ($question['points_earned'] !== null) {
        $pointsEarned += $question['points_earned'];
    }
}

$percentage = $totalPoints > 0 ? round(($pointsEarned / $totalPoints) * 100, 2) : 0;

$pageTitle = "Quiz Results: " . htmlspecialchars($attempt['quiz_title']);
require_once '../../includes/header.php';
?>

<div class="quiz-results">
    <h1>Quiz Results: <?php echo htmlspecialchars($attempt['quiz_title']); ?></h1>
    <p class="course">Course: <?php echo htmlspecialchars($attempt['course_title']); ?></p>
    
    <div class="results-summary">
        <div class="score-card">
            <h3>Your Score</h3>
            <div class="score"><?php echo $pointsEarned; ?>/<?php echo $totalPoints; ?></div>
            <div class="percentage"><?php echo $percentage; ?>%</div>
            <p>Completed on: <?php echo formatDate($attempt['completed_at']); ?></p>
        </div>
    </div>
    
    <div class="results-details">
        <h2>Question Review</h2>
        
        <?php foreach ($questions as $index => $question): ?>
            <div class="question-review <?php echo $question['is_correct'] ? 'correct' : 'incorrect'; ?>">
                <h3>Question <?php echo $index + 1; ?></h3>
                <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                
                <div class="student-answer">
                    <h4>Your Answer:</h4>
                    <p><?php echo htmlspecialchars($question['answer_text'] ?? 'No answer provided'); ?></p>
                </div>
                
                <?php if ($question['question_type'] !== 'short_answer'): ?>
                    <div class="correct-answer">
                        <h4>Correct Answer:</h4>
                        <?php 
                            $correctOptions = $db->query("
                                SELECT option_text FROM quiz_question_options
                                WHERE question_id = {$question['question_id']}
                                AND is_correct = 1
                            ")->fetch_all(MYSQLI_ASSOC);
                            
                            foreach ($correctOptions as $option) {
                                echo '<p>' . htmlspecialchars($option['option_text']) . '</p>';
                            }
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="points">
                    <p>
                        Points: <?php echo $question['points_earred'] ?? '0'; ?>/<?php echo $question['points']; ?>
                        <?php if ($question['is_correct']): ?>
                            <span class="badge bg-success">Correct</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Incorrect</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="results-actions">
        <a href="my_quizzes.php" class="btn btn-primary">Back to My Quizzes</a>
        <a href="../courses/view_course.php?id=<?php echo $attempt['course_id']; ?>" class="btn btn-secondary">Back to Course</a>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
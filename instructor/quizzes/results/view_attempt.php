<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('../../manage_quizzes.php');
}

$attemptId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get attempt data and verify ownership
$attempt = $db->query("
    SELECT a.*, u.first_name, u.last_name, u.email,
           q.title as quiz_title, q.time_limit,
           c.title as course_title
    FROM quiz_attempts a
    JOIN users u ON a.student_id = u.user_id
    JOIN quizzes q ON a.quiz_id = q.quiz_id
    JOIN courses c ON q.course_id = c.course_id
    WHERE a.attempt_id = $attemptId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$attempt) {
    redirect('../../manage_quizzes.php');
}

// Get all questions for this quiz
$questions = $db->query("
    SELECT q.* 
    FROM quiz_questions q
    WHERE q.quiz_id = {$attempt['quiz_id']}
    ORDER BY q.position ASC
")->fetch_all(MYSQLI_ASSOC);

// Get student's answers
$answers = [];
$result = $db->query("
    SELECT a.*, q.question_text, q.question_type, q.points
    FROM quiz_answers a
    JOIN quiz_questions q ON a.question_id = q.question_id
    WHERE a.attempt_id = $attemptId
");

while ($row = $result->fetch_assoc()) {
    $answers[$row['question_id']] = $row;
}

$pageTitle = 'Quiz Attempt';
require_once '../../../includes/header.php';
?>

<div class="view-attempt">
    <h1>Quiz Attempt</h1>
    <p>Quiz: <?php echo htmlspecialchars($attempt['quiz_title']); ?></p>
    <p>Course: <?php echo htmlspecialchars($attempt['course_title']); ?></p>
    <p>Student: <?php echo htmlspecialchars($attempt['first_name'] . ' ' . $attempt['last_name']); ?></p>
    <p>Email: <?php echo htmlspecialchars($attempt['email']); ?></p>
    <p>Started: <?php echo formatDate($attempt['started_at']); ?></p>
    <p>Completed: <?php echo $attempt['completed_at'] ? formatDate($attempt['completed_at']) : '<span class="status pending">Incomplete</span>'; ?></p>
    <p>Score: <?php echo $attempt['score'] !== null ? $attempt['score'] . '%' : '<span class="status pending">Not scored</span>'; ?></p>
    
    <div class="quiz-questions">
        <h2>Questions and Answers</h2>
        
        <?php foreach ($questions as $question): ?>
            <div class="question-card">
                <h3><?php echo htmlspecialchars($question['question_text']); ?></h3>
                <p>Points: <?php echo $question['points']; ?></p>
                <p>Type: <?php 
                    switch($question['question_type']) {
                        case 'multiple_choice': echo 'Multiple Choice'; break;
                        case 'true_false': echo 'True/False'; break;
                        case 'short_answer': echo 'Short Answer'; break;
                        default: echo $question['question_type'];
                    }
                ?></p>
                
                <?php if (isset($answers[$question['question_id']])): ?>
                    <div class="student-answer">
                        <h4>Student Answer:</h4>
                        <p><?php echo htmlspecialchars($answers[$question['question_id']]['answer_text']); ?></p>
                        
                        <?php if ($question['question_type'] !== 'short_answer'): ?>
                            <p>Correct: <?php echo $answers[$question['question_id']]['is_correct'] ? 'Yes' : 'No'; ?></p>
                        <?php endif; ?>
                        
                        <p>Points Earned: <?php echo $answers[$question['question_id']]['points_earned'] ?? '0'; ?> / <?php echo $question['points']; ?></p>
                    </div>
                <?php else: ?>
                    <div class="student-answer">
                        <p class="status pending">No answer provided</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="action-bar">
        <a href="quiz_results.php?id=<?php echo $attempt['quiz_id']; ?>" class="btn">Back to Results</a>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>
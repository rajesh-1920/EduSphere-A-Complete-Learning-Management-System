<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

if (!isset($_GET['id'])) {
    redirect('my_quizzes.php');
}

$quizId = $_GET['id'];
$studentId = $_SESSION['user_id'];
$db = new Database();

// Get quiz details and verify student is enrolled
$quiz = $db->query("
    SELECT q.*, c.title as course_title
    FROM quizzes q
    JOIN courses c ON q.course_id = c.course_id
    JOIN enrollments e ON q.course_id = e.course_id
    WHERE q.quiz_id = $quizId
    AND e.student_id = $studentId
    AND e.status = 'active'
")->fetch_assoc();

if (!$quiz) {
    $_SESSION['error'] = "Quiz not found or you are not enrolled in this course.";
    redirect('my_quizzes.php');
}

// Check if quiz is available
$now = time();
$availableFrom = $quiz['available_from'] ? strtotime($quiz['available_from']) : 0;
$availableTo = $quiz['available_to'] ? strtotime($quiz['available_to']) : PHP_INT_MAX;

if ($now < $availableFrom || $now > $availableTo) {
    $_SESSION['error'] = "This quiz is not currently available.";
    redirect('my_quizzes.php');
}

// Get student's attempts
$attempts = $db->query("
    SELECT * FROM quiz_attempts
    WHERE quiz_id = $quizId
    AND student_id = $studentId
    ORDER BY started_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Check for active attempt (started but not completed)
$activeAttempt = null;
foreach ($attempts as $attempt) {
    if ($attempt['completed_at'] === null) {
        $activeAttempt = $attempt;
        break;
    }
}

$pageTitle = "Quiz: " . htmlspecialchars($quiz['title']);
require_once '../../includes/header.php';
?>

<div class="quiz-view">
    <div class="quiz-header">
        <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
        <p class="course">Course: <?php echo htmlspecialchars($quiz['course_title']); ?></p>
        
        <div class="quiz-meta">
            <?php if ($quiz['available_from']): ?>
                <p><strong>Available from:</strong> <?php echo formatDate($quiz['available_from']); ?></p>
            <?php endif; ?>
            
            <?php if ($quiz['available_to']): ?>
                <p><strong>Available until:</strong> <?php echo formatDate($quiz['available_to']); ?></p>
            <?php endif; ?>
            
            <?php if ($quiz['time_limit']): ?>
                <p><strong>Time limit:</strong> <?php echo $quiz['time_limit']; ?> minutes</p>
            <?php endif; ?>
        </div>
        
        <div class="quiz-description">
            <?php echo nl2br(htmlspecialchars($quiz['description'])); ?>
        </div>
    </div>

    <div class="attempts-section">
        <h2>Your Attempts</h2>
        
        <?php if (!empty($attempts)): ?>
            <table class="attempts-table">
                <thead>
                    <tr>
                        <th>Started</th>
                        <th>Completed</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attempts as $attempt): ?>
                        <tr>
                            <td><?php echo formatDate($attempt['started_at']); ?></td>
                            <td><?php echo $attempt['completed_at'] ? formatDate($attempt['completed_at']) : 'In Progress'; ?></td>
                            <td>
                                <?php if ($attempt['completed_at']): ?>
                                    <span class="badge bg-success">Completed</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">In Progress</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($attempt['completed_at'] && $attempt['score'] !== null): ?>
                                    <?php echo $attempt['score']; ?>
                                <?php elseif ($attempt['completed_at']): ?>
                                    Pending
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($attempt['completed_at']): ?>
                                    <a href="quiz_results.php?attempt_id=<?php echo $attempt['attempt_id']; ?>" class="btn btn-sm btn-primary">View Results</a>
                                <?php else: ?>
                                    <a href="take_quiz.php?attempt_id=<?php echo $attempt['attempt_id']; ?>" class="btn btn-sm btn-warning">Continue</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">
                You haven't attempted this quiz yet.
            </div>
        <?php endif; ?>
    </div>

    <div class="quiz-actions">
        <?php if ($activeAttempt): ?>
            <a href="take_quiz.php?attempt_id=<?php echo $activeAttempt['attempt_id']; ?>" class="btn btn-lg btn-warning">
                <i class="fas fa-play-circle"></i> Continue Your Attempt
            </a>
        <?php else: ?>
            <?php 
                // Check if maximum attempts reached (if quiz has attempt limit)
                $maxAttempts = 3; // This should come from quiz settings
                $attemptCount = count($attempts);
                
                if ($maxAttempts > 0 && $attemptCount >= $maxAttempts): ?>
                <div class="alert alert-danger">
                    You have reached the maximum number of attempts (<?php echo $maxAttempts; ?>) for this quiz.
                </div>
            <?php else: ?>
                <a href="take_quiz.php?id=<?php echo $quizId; ?>" class="btn btn-lg btn-primary">
                    <i class="fas fa-play-circle"></i> Start New Attempt
                </a>
                
                <?php if ($maxAttempts > 0): ?>
                    <p class="attempts-remaining">
                        Attempts remaining: <?php echo $maxAttempts - $attemptCount; ?> of <?php echo $maxAttempts; ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="quiz-instructions mt-4">
        <h3>Instructions</h3>
        <ul>
            <li>Ensure you have a stable internet connection before starting</li>
            <li>You cannot pause the quiz once started</li>
            <li>For timed quizzes, the timer will continue even if you close the browser</li>
            <li>Answers are auto-saved as you progress</li>
            <li>Review all questions before submitting</li>
        </ul>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
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
    SELECT q.*, c.title as course_title, u.first_name, u.last_name
    FROM quizzes q
    JOIN courses c ON q.course_id = c.course_id
    JOIN users u ON c.instructor_id = u.user_id
    WHERE q.quiz_id = $quizId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$quiz) {
    redirect('manage_quizzes.php');
}

// Get question count
$questionCount = $db->query("
    SELECT COUNT(*) FROM quiz_questions 
    WHERE quiz_id = $quizId
")->fetch_row()[0];

// Get attempt count
$attemptCount = $db->query("
    SELECT COUNT(*) FROM quiz_attempts 
    WHERE quiz_id = $quizId
")->fetch_row()[0];

$pageTitle = 'View Quiz';
require_once '../../includes/header.php';
?>

<div class="view-quiz">
    <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
    <p>Course: <?php echo htmlspecialchars($quiz['course_title']); ?></p>
    <p>Instructor: <?php echo htmlspecialchars($quiz['first_name'] . ' ' . $quiz['last_name']); ?></p>
    <p>Time Limit: <?php echo $quiz['time_limit'] > 0 ? $quiz['time_limit'] . ' minutes' : 'No limit'; ?></p>
    <p>Available: <?php echo formatDate($quiz['available_from']); ?> to <?php echo formatDate($quiz['available_to']); ?></p>
    <p>Questions: <?php echo $questionCount; ?></p>
    <p>Attempts: <?php echo $attemptCount; ?></p>
    
    <div class="quiz-content">
        <h3>Description</h3>
        <div class="content-box">
            <?php echo nl2br(htmlspecialchars($quiz['description'])); ?>
        </div>
    </div>
    
    <div class="action-bar">
        <a href="edit_quiz.php?id=<?php echo $quizId; ?>" class="btn">Edit</a>
        <a href="questions/manage_questions.php?id=<?php echo $quizId; ?>" class="btn">Manage Questions</a>
        <a href="results/quiz_results.php?id=<?php echo $quizId; ?>" class="btn">View Results</a>
        <a href="manage_quizzes.php" class="btn">Back to Quizzes</a>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
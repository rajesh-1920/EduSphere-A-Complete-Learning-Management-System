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

// Get attempts for this quiz
$attempts = $db->query("
    SELECT a.*, u.first_name, u.last_name, u.email
    FROM quiz_attempts a
    JOIN users u ON a.student_id = u.user_id
    WHERE a.quiz_id = $quizId
    ORDER BY a.started_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Calculate average score
$averageScore = $db->query("
    SELECT AVG(score) FROM quiz_attempts 
    WHERE quiz_id = $quizId AND score IS NOT NULL
")->fetch_row()[0];

$pageTitle = 'Quiz Results';
require_once '../../../includes/header.php';
?>

<div class="quiz-results">
    <h1>Quiz Results: <?php echo htmlspecialchars($quiz['title']); ?></h1>
    <p>Course: <?php echo htmlspecialchars($quiz['course_title']); ?></p>
    
    <div class="stats">
        <div class="stat-card">
            <h3>Attempts</h3>
            <p><?php echo count($attempts); ?></p>
        </div>
        <div class="stat-card">
            <h3>Average Score</h3>
            <p><?php echo $averageScore ? number_format($averageScore, 2) : 'N/A'; ?></p>
        </div>
    </div>
    
    <div class="action-bar">
        <a href="../../view_quiz.php?id=<?php echo $quizId; ?>" class="btn">Back to Quiz</a>
    </div>
    
    <?php if (!empty($attempts)): ?>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Started</th>
                    <th>Completed</th>
                    <th>Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attempts as $attempt): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($attempt['first_name'] . ' ' . $attempt['last_name']); ?>
                            <br><small><?php echo htmlspecialchars($attempt['email']); ?></small>
                        </td>
                        <td><?php echo formatDate($attempt['started_at']); ?></td>
                        <td>
                            <?php if ($attempt['completed_at']): ?>
                                <?php echo formatDate($attempt['completed_at']); ?>
                            <?php else: ?>
                                <span class="status pending">Incomplete</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($attempt['score'] !== null): ?>
                                <?php echo $attempt['score']; ?>%
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="view_attempt.php?id=<?php echo $attempt['attempt_id']; ?>" class="btn">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No attempts for this quiz yet.</p>
    <?php endif; ?>
</div>

<?php require_once '../../../includes/footer.php'; ?>
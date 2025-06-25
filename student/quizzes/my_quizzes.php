<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

$studentId = $_SESSION['user_id'];
$db = new Database();

// Get quizzes with attempt status
$quizzes = $db->query("
    SELECT q.*, c.title as course_title, 
           a.attempt_id, a.score, a.completed_at,
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.quiz_id) as total_attempts
    FROM quizzes q
    JOIN courses c ON q.course_id = c.course_id
    JOIN enrollments e ON q.course_id = e.course_id
    LEFT JOIN quiz_attempts a ON q.quiz_id = a.quiz_id AND a.student_id = $studentId
    WHERE e.student_id = $studentId AND e.status = 'active'
    AND (q.available_from IS NULL OR q.available_from <= NOW())
    AND (q.available_to IS NULL OR q.available_to >= NOW())
    ORDER BY q.available_to ASC, q.title ASC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'My Quizzes';
require_once '../../includes/header.php';
?>

<div class="my-quizzes">
    <h1>My Quizzes</h1>
    
    <?php if (!empty($quizzes)): ?>
        <table class="quizzes-table">
            <thead>
                <tr>
                    <th>Quiz</th>
                    <th>Course</th>
                    <th>Available Until</th>
                    <th>Time Limit</th>
                    <th>Attempts</th>
                    <th>Your Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['course_title']); ?></td>
                        <td><?php echo $quiz['available_to'] ? formatDate($quiz['available_to']) : 'No limit'; ?></td>
                        <td><?php echo $quiz['time_limit'] ? $quiz['time_limit'] . ' mins' : 'No limit'; ?></td>
                        <td><?php echo $quiz['total_attempts']; ?></td>
                        <td>
                            <?php if ($quiz['attempt_id']): ?>
                                <?php echo $quiz['score'] !== null ? $quiz['score'] : 'Pending'; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="view_quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn">View</a>
                            <?php if (!$quiz['attempt_id'] || $quiz['completed_at'] === null): ?>
                                <a href="take_quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn">Take Quiz</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No quizzes available at this time.</p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
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

// Get questions for this quiz
$questions = $db->query("
    SELECT * FROM quiz_questions 
    WHERE quiz_id = $quizId
    ORDER BY position ASC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Questions';
require_once '../../../includes/header.php';
?>

<div class="manage-questions">
    <h1>Manage Questions</h1>
    <p>Quiz: <?php echo htmlspecialchars($quiz['title']); ?></p>
    <p>Course: <?php echo htmlspecialchars($quiz['course_title']); ?></p>
    
    <div class="action-bar">
        <a href="add_question.php?id=<?php echo $quizId; ?>" class="btn">Add Question</a>
        <a href="../../view_quiz.php?id=<?php echo $quizId; ?>" class="btn">Back to Quiz</a>
    </div>
    
    <?php if (!empty($questions)): ?>
        <table>
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Question</th>
                    <th>Type</th>
                    <th>Points</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questions as $question): ?>
                    <tr>
                        <td><?php echo $question['position']; ?></td>
                        <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                        <td><?php 
                            switch($question['question_type']) {
                                case 'multiple_choice': echo 'Multiple Choice'; break;
                                case 'true_false': echo 'True/False'; break;
                                case 'short_answer': echo 'Short Answer'; break;
                                default: echo $question['question_type'];
                            }
                        ?></td>
                        <td><?php echo $question['points']; ?></td>
                        <td>
                            <a href="edit_question.php?id=<?php echo $question['question_id']; ?>" class="btn">Edit</a>
                            <a href="delete_question.php?id=<?php echo $question['question_id']; ?>" class="btn delete-btn">Delete</a>
                            <?php if ($question['question_type'] !== 'short_answer'): ?>
                                <a href="manage_options.php?id=<?php echo $question['question_id']; ?>" class="btn">Options</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No questions added yet. <a href="add_question.php?id=<?php echo $quizId; ?>">Add your first question</a></p>
    <?php endif; ?>
</div>

<?php require_once '../../../includes/footer.php'; ?>
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

if (!$question || $question['question_type'] === 'short_answer') {
    redirect('../../manage_quizzes.php');
}

// Get options for this question
$options = $db->query("
    SELECT * FROM quiz_question_options 
    WHERE question_id = $questionId
    ORDER BY option_id ASC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Options';
require_once '../../../includes/header.php';
?>

<div class="manage-options">
    <h1>Manage Options</h1>
    <p>Quiz: <?php echo htmlspecialchars($question['quiz_title']); ?></p>
    <p>Course: <?php echo htmlspecialchars($question['course_title']); ?></p>
    <p>Question: <?php echo htmlspecialchars($question['question_text']); ?></p>
    
    <div class="action-bar">
        <a href="add_option.php?id=<?php echo $questionId; ?>" class="btn">Add Option</a>
        <a href="edit_question.php?id=<?php echo $questionId; ?>" class="btn">Back to Question</a>
    </div>
    
    <?php if (!empty($options)): ?>
        <table>
            <thead>
                <tr>
                    <th>Option</th>
                    <th>Correct?</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($options as $option): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($option['option_text']); ?></td>
                        <td><?php echo $option['is_correct'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <a href="edit_option.php?id=<?php echo $option['option_id']; ?>" class="btn">Edit</a>
                            <a href="delete_option.php?id=<?php echo $option['option_id']; ?>" class="btn delete-btn">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No options added yet. <a href="add_option.php?id=<?php echo $questionId; ?>">Add your first option</a></p>
    <?php endif; ?>
</div>

<?php require_once '../../../includes/footer.php'; ?>
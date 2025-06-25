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
    SELECT q.question_id, q.quiz_id 
    FROM quiz_questions q
    JOIN quizzes qz ON q.quiz_id = qz.quiz_id
    JOIN courses c ON qz.course_id = c.course_id
    WHERE q.question_id = $questionId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$question) {
    redirect('../../manage_quizzes.php');
}

// Delete the question
$stmt = $db->prepare("DELETE FROM quiz_questions WHERE question_id = ?");
$stmt->bind_param("i", $questionId);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Question deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Failed to delete question. Please try again.';
}

redirect("manage_questions.php?id={$question['quiz_id']}");
?>
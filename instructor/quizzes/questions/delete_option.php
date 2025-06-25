<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('../../manage_quizzes.php');
}

$optionId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get option data and verify ownership
$option = $db->query("
    SELECT o.option_id, o.question_id 
    FROM quiz_question_options o
    JOIN quiz_questions q ON o.question_id = q.question_id
    JOIN quizzes qz ON q.quiz_id = qz.quiz_id
    JOIN courses c ON qz.course_id = c.course_id
    WHERE o.option_id = $optionId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$option) {
    redirect('../../manage_quizzes.php');
}

// Don't allow deleting True/False options
$questionType = $db->query("
    SELECT question_type FROM quiz_questions 
    WHERE question_id = {$option['question_id']}
")->fetch_row()[0];

if ($questionType === 'true_false') {
    $_SESSION['error_message'] = 'Cannot delete True/False options. Edit the question type instead.';
    redirect("manage_options.php?id={$option['question_id']}");
}

// Delete the option
$stmt = $db->prepare("DELETE FROM quiz_question_options WHERE option_id = ?");
$stmt->bind_param("i", $optionId);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Option deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Failed to delete option. Please try again.';
}

redirect("manage_options.php?id={$option['question_id']}");
?>
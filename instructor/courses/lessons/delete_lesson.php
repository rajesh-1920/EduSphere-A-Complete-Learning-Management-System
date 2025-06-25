<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('../../manage_courses.php');
}

$lessonId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get lesson data and verify ownership
$lesson = $db->query("
    SELECT l.lesson_id, l.module_id 
    FROM lessons l
    JOIN modules m ON l.module_id = m.module_id
    JOIN courses c ON m.course_id = c.course_id
    WHERE l.lesson_id = $lessonId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$lesson) {
    redirect('../../manage_courses.php');
}

// Delete the lesson
$stmt = $db->prepare("DELETE FROM lessons WHERE lesson_id = ?");
$stmt->bind_param("i", $lessonId);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Lesson deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Failed to delete lesson. Please try again.';
}

redirect("manage_lessons.php?module_id={$lesson['module_id']}");
?>
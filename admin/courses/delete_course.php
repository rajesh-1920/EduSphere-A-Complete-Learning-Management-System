<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['admin']);

if (!isset($_GET['id'])) {
    redirect('manage_courses.php');
}

$courseId = $_GET['id'];
$db = new Database();

// Get course data to check thumbnail
$course = $db->query("SELECT thumbnail FROM courses WHERE course_id = $courseId")->fetch_assoc();

// Delete the course
$stmt = $db->prepare("DELETE FROM courses WHERE course_id = ?");
$stmt->bind_param("i", $courseId);

if ($stmt->execute()) {
    // Delete thumbnail if it exists
    if ($course && $course['thumbnail']) {
        @unlink(COURSE_THUMBNAIL_PATH . $course['thumbnail']);
    }
    $_SESSION['success_message'] = 'Course deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Failed to delete course. Please try again.';
}

redirect('manage_courses.php');
?>
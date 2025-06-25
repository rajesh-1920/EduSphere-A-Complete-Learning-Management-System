<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('manage_discussions.php');
}

$discussionId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get discussion data and verify ownership
$discussion = $db->query("
    SELECT d.discussion_id 
    FROM discussions d
    JOIN courses c ON d.course_id = c.course_id
    WHERE d.discussion_id = $discussionId AND c.instructor_id = $instructorId
")->fetch_row();

if (!$discussion) {
    redirect('manage_discussions.php');
}

// Delete the discussion and its replies
$db->query("DELETE FROM discussion_replies WHERE discussion_id = $discussionId");
$stmt = $db->prepare("DELETE FROM discussions WHERE discussion_id = ?");
$stmt->bind_param("i", $discussionId);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Discussion deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Failed to delete discussion. Please try again.';
}

redirect('manage_discussions.php');
?>
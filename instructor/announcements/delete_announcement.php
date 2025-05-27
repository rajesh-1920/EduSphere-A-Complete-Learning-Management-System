<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('manage_announcements.php');
}

$announcementId = $_GET['id'];
$instructorId = $_SESSION['user_id'];
$db = new Database();

// Verify the announcement belongs to a course taught by this instructor
$announcement = $db->query("
    SELECT a.announcement_id
    FROM announcements a
    JOIN courses c ON a.course_id = c.course_id
    WHERE a.announcement_id = $announcementId AND c.instructor_id = $instructorId
")->fetch_row();

if (!$announcement) {
    $_SESSION['error_message'] = 'Announcement not found or you do not have permission to delete it.';
    redirect('manage_announcements.php');
}

// Delete the announcement
$stmt = $db->prepare("DELETE FROM announcements WHERE announcement_id = ?");
$stmt->bind_param("i", $announcementId);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Announcement deleted successfully.';
} else {
    $_SESSION['error_message'] = 'Failed to delete announcement. Please try again.';
}

redirect('manage_announcements.php');

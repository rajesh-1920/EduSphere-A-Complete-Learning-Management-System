<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('../manage_courses.php');
}

$moduleId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get module data and verify ownership
$module = $db->query("
    SELECT m.module_id, m.course_id 
    FROM modules m
    JOIN courses c ON m.course_id = c.course_id
    WHERE m.module_id = $moduleId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$module) {
    redirect('../manage_courses.php');
}

// Delete the module
$stmt = $db->prepare("DELETE FROM modules WHERE module_id = ?");
$stmt->bind_param("i", $moduleId);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Module deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Failed to delete module. Please try again.';
}

redirect("manage_modules.php?course_id={$module['course_id']}");
?>
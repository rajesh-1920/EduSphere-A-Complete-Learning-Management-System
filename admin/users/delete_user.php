<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['admin']);

if (!isset($_GET['id'])) {
    redirect('manage_users.php');
}

$userId = $_GET['id'];
$user = getUserById($userId);

if (!$user) {
    redirect('manage_users.php');
}

// Check if the user is trying to delete themselves
if ($user['user_id'] == $_SESSION['user_id']) {
    $_SESSION['error_message'] = 'You cannot delete your own account!';
    redirect('manage_users.php');
}

// Delete the user
$stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    // Delete profile picture if it's not the default
    if ($user['profile_picture'] && $user['profile_picture'] !== 'default.png') {
        @unlink(PROFILE_PICTURE_PATH . $user['profile_picture']);
    }
    $_SESSION['success_message'] = 'User deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Failed to delete user. Please try again.';
}

redirect('manage_users.php');
?>
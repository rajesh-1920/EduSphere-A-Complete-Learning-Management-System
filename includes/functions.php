<?php
require_once 'db_connect.php';

// Redirect to another page
function redirect($url) {
    header("Location: $url");
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function checkRole($allowedRoles) {
    if (!isLoggedIn() || !in_array($_SESSION['role'], $allowedRoles)) {
        redirect('login.php');
    }
}

// Sanitize input data
function sanitize($data) {
    global $db;
    return $db->escapeString(htmlspecialchars(trim($data)));
}

// Get user data
function getUserById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get course data
function getCourseById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Upload file helper
function uploadFile($file, $targetDir, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }

    // Check file type
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $targetPath = $targetDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

// Format date for display
function formatDate($dateString) {
    return date('M j, Y g:i A', strtotime($dateString));
}
?>
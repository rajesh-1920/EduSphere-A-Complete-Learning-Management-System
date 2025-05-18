<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'EduSphere');

// Site configuration
define('SITE_NAME', 'EduSphere');
define('SITE_URL', 'http://localhost/EduSphere');
define('UPLOAD_DIR', 'uploads/');

// Start session
session_start();

// Create database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
require_once 'functions.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user has specific role
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Redirect if doesn't have required role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header("Location: unauthorized.php");
        exit();
    }
}

// Sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Upload file helper
function uploadFile($file, $directory = '') {
    $targetDir = UPLOAD_DIR . $directory;
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($file["name"]);
    $targetPath = $targetDir . $fileName;
    
    if (move_uploaded_file($file["tmp_name"], $targetPath)) {
        return $targetPath;
    }
    return false;
}
?>
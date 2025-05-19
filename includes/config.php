<?php
// Configuration settings for EduSphere

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'edusphere');

// Site configuration
define('SITE_NAME', 'EduSphere');
define('SITE_URL', 'http://localhost/edusphere');
define('SITE_ROOT', dirname(dirname(__FILE__)));

// File upload paths
define('UPLOAD_PATH', SITE_ROOT . '/uploads/');
define('PROFILE_PICTURE_PATH', UPLOAD_PATH . 'profile_pictures/');
define('COURSE_THUMBNAIL_PATH', UPLOAD_PATH . 'course_thumbnails/');
define('ASSIGNMENT_UPLOAD_PATH', UPLOAD_PATH . 'assignments/');

// Ensure upload directories exist
if (!file_exists(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
if (!file_exists(PROFILE_PICTURE_PATH)) mkdir(PROFILE_PICTURE_PATH, 0755, true);
if (!file_exists(COURSE_THUMBNAIL_PATH)) mkdir(COURSE_THUMBNAIL_PATH, 0755, true);
if (!file_exists(ASSIGNMENT_UPLOAD_PATH)) mkdir(ASSIGNMENT_UPLOAD_PATH, 0755, true);

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('UTC');
?>
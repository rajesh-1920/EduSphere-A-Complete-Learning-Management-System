<?php
require_once 'config.php';
requireRole('instructor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: instructor_dashboard.php");
    exit();
}

$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
$date = isset($_POST['date']) ? $_POST['date'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Validate inputs
if (empty($course_id) || empty($student_id) || empty($date) || empty($status)) {
    $_SESSION['error_message'] = 'All fields are required.';
    header("Location: student_attendance.php?course_id=$course_id&student_id=$student_id");
    exit();
}

// Verify course and student
$course = getCourseById($course_id);
if (!$course || $course['instructor_id'] != $_SESSION['user_id']) {
    header("Location: instructor_dashboard.php");
    exit();
}

$student = getUserById($student_id);
if (!$student || $student['role'] != 'student') {
    header("Location: attendance_report.php?course_id=$course_id");
    exit();
}

// Record attendance
if (recordAttendance($course_id, $student_id, $date, $status, $_SESSION['user_id'])) {
    // Recalculate results
    calculateAttendanceResults($student_id, $course_id);
    
    $_SESSION['success_message'] = 'Attendance record updated successfully!';
} else {
    $_SESSION['error_message'] = 'Failed to update attendance record.';
}

header("Location: student_attendance.php?course_id=$course_id&student_id=$student_id");
exit();
?>
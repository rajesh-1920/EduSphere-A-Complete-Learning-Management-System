<?php
require_once 'config.php';
requireRole('instructor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: instructor_dashboard.php");
    exit();
}

$attendance_id = isset($_POST['attendance_id']) ? intval($_POST['attendance_id']) : 0;
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;

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

// Delete attendance record
try {
    $stmt = $pdo->prepare("DELETE FROM attendance WHERE attendance_id = ?");
    $stmt->execute([$attendance_id]);
    
    // Recalculate results
    calculateAttendanceResults($student_id, $course_id);
    
    $_SESSION['success_message'] = 'Attendance record deleted successfully!';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Failed to delete attendance record.';
}

header("Location: student_attendance.php?course_id=$course_id&student_id=$student_id");
exit();
?>
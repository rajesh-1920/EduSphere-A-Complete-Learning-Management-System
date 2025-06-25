<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

$db = new Database();

// Verify the course belongs to the instructor
$course = $db->query("
    SELECT title FROM courses 
    WHERE course_id = $courseId AND instructor_id = $instructorId
")->fetch_assoc();

if (!$course) {
    die("Invalid course or access denied.");
}

// Get attendance data
$attendanceData = $db->query("
    SELECT 
        u.first_name, 
        u.last_name,
        a.date,
        a.status,
        a.notes,
        CONCAT(ru.first_name, ' ', ru.last_name) as recorded_by
    FROM attendance a
    JOIN users u ON a.student_id = u.user_id
    JOIN users ru ON a.recorded_by = ru.user_id
    JOIN enrollments e ON a.student_id = e.student_id AND a.course_id = e.course_id
    WHERE a.course_id = $courseId 
    AND e.status = 'active'
    AND a.date BETWEEN '$startDate' AND '$endDate'
    ORDER BY u.last_name, u.first_name, a.date
")->fetch_all(MYSQLI_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_' . $course['title'] . '_' . $startDate . '_to_' . $endDate . '.csv"');

// Create output file pointer
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, [
    'Student Name', 
    'Date', 
    'Status', 
    'Notes', 
    'Recorded By'
]);

// Write data rows
foreach ($attendanceData as $row) {
    fputcsv($output, [
        $row['first_name'] . ' ' . $row['last_name'],
        $row['date'],
        ucfirst($row['status']),
        $row['notes'],
        $row['recorded_by']
    ]);
}

fclose($output);
exit;
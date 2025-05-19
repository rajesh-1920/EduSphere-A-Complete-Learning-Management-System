<?php
// Get user by ID
function getUserById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all courses
function getAllCourses($instructor_id = null)
{
    global $pdo;
    $sql = "SELECT c.*, u.full_name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.user_id";
    if ($instructor_id) {
        $sql .= " WHERE c.instructor_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$instructor_id]);
    } else {
        $stmt = $pdo->query($sql);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get course by ID
function getCourseById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT c.*, u.full_name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.user_id WHERE c.course_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get enrolled courses for student
function getEnrolledCourses($student_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT c.*, u.full_name as instructor_name FROM enrollments e JOIN courses c ON e.course_id = c.course_id JOIN users u ON c.instructor_id = u.user_id WHERE e.student_id = ?");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if student is enrolled in course
function isEnrolled($student_id, $course_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
    $stmt->execute([$student_id, $course_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
}

// Get modules for course
function getModules($course_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY sequence");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get lessons for module
function getLessons($module_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE module_id = ? ORDER BY sequence");
    $stmt->execute([$module_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get assignments for course
function getAssignments($course_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE course_id = ? ORDER BY created_at DESC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get announcements for course
function getAnnouncements($course_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, u.full_name as author_name FROM announcements a JOIN users u ON a.author_id = u.user_id WHERE a.course_id = ? ORDER BY created_at DESC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get discussions for course
function getDiscussions($course_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT d.*, u.full_name as user_name, u.profile_picture FROM discussions d JOIN users u ON d.user_id = u.user_id WHERE d.course_id = ? ORDER BY created_at DESC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get replies for discussion
function getDiscussionReplies($discussion_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT r.*, u.full_name as user_name, u.profile_picture FROM discussion_replies r JOIN users u ON r.user_id = u.user_id WHERE r.discussion_id = ? ORDER BY created_at ASC");
    $stmt->execute([$discussion_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get quizzes for course
function getQuizzes($course_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ? ORDER BY created_at DESC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get quiz attempts for user
function getQuizAttempts($quiz_id, $user_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quiz_attempts WHERE quiz_id = ? AND user_id = ? ORDER BY started_at DESC");
    $stmt->execute([$quiz_id, $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Count students enrolled in course
function countEnrolledStudents($course_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
}

// Get course progress for student
function getCourseProgress($student_id, $course_id)
{
    global $pdo;
    // Count total lessons in course
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM lessons l JOIN modules m ON l.module_id = m.module_id WHERE m.course_id = ?");
    $stmt->execute([$course_id]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Count completed lessons (you'll need to implement a completion tracking system)
    $completed = 0; // Placeholder

    return $total > 0 ? round(($completed / $total) * 100) : 0;
}

// Get attendance for a course on a specific date
function getAttendanceByDate($course_id, $date)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, u.full_name as student_name 
                          FROM attendance a 
                          JOIN users u ON a.student_id = u.user_id 
                          WHERE a.course_id = ? AND a.date = ?");
    $stmt->execute([$course_id, $date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all attendance records for a student in a course
function getStudentAttendance($student_id, $course_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM attendance 
                          WHERE student_id = ? AND course_id = ? 
                          ORDER BY date DESC");
    $stmt->execute([$student_id, $course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Record attendance
function recordAttendance($course_id, $student_id, $date, $status, $recorded_by)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO attendance 
                             (course_id, student_id, date, status, recorded_by) 
                             VALUES (?, ?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE status = VALUES(status)");
        return $stmt->execute([$course_id, $student_id, $date, $status, $recorded_by]);
    } catch (PDOException $e) {
        return false;
    }
}

// Get attendance rules for a course
function getAttendanceRules($course_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM attendance_rules WHERE course_id = ?");
    $stmt->execute([$course_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Set attendance rules for a course
function setAttendanceRules($course_id, $min_presence, $grade_adjustment, $max_grade)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO attendance_rules 
                             (course_id, min_presence_percentage, grade_adjustment, max_grade_with_attendance) 
                             VALUES (?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE 
                             min_presence_percentage = VALUES(min_presence_percentage),
                             grade_adjustment = VALUES(grade_adjustment),
                             max_grade_with_attendance = VALUES(max_grade_with_attendance)");
        return $stmt->execute([$course_id, $min_presence, $grade_adjustment, $max_grade]);
    } catch (PDOException $e) {
        return false;
    }
}

// Calculate attendance-based results for a student in a course
function calculateAttendanceResults($student_id, $course_id)
{
    global $pdo;

    // Get total classes
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT date) as total_classes 
                          FROM attendance 
                          WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $total_classes = $stmt->fetch(PDO::FETCH_ASSOC)['total_classes'];

    // Get attended classes
    $stmt = $pdo->prepare("SELECT COUNT(*) as attended_classes 
                          FROM attendance 
                          WHERE course_id = ? AND student_id = ? AND status IN ('present', 'late')");
    $stmt->execute([$course_id, $student_id]);
    $attended_classes = $stmt->fetch(PDO::FETCH_ASSOC)['attended_classes'];

    // Calculate percentage
    $attendance_percentage = $total_classes > 0 ? round(($attended_classes / $total_classes) * 100, 2) : 0;

    // Get rules
    $rules = getAttendanceRules($course_id);
    $min_presence = $rules['min_presence_percentage'] ?? 75;
    $grade_adjustment = $rules['grade_adjustment'] ?? 0;
    $max_grade = $rules['max_grade_with_attendance'] ?? 100;

    // Calculate final grade
    $final_grade = $max_grade;
    if ($attendance_percentage < $min_presence) {
        $final_grade = max(0, $max_grade - $grade_adjustment);
    }

    // Store results
    try {
        $stmt = $pdo->prepare("INSERT INTO attendance_results 
                             (course_id, student_id, total_classes, attended_classes, 
                              attendance_percentage, grade_adjustment, final_grade) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE 
                             total_classes = VALUES(total_classes),
                             attended_classes = VALUES(attended_classes),
                             attendance_percentage = VALUES(attendance_percentage),
                             grade_adjustment = VALUES(grade_adjustment),
                             final_grade = VALUES(final_grade)");
        return $stmt->execute([
            $course_id,
            $student_id,
            $total_classes,
            $attended_classes,
            $attendance_percentage,
            $grade_adjustment,
            $final_grade
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

// Get attendance results for a student
function getAttendanceResult($student_id, $course_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM attendance_results 
                          WHERE student_id = ? AND course_id = ?");
    $stmt->execute([$student_id, $course_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

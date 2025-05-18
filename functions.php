<?php
// Get user by ID
function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all courses
function getAllCourses($instructor_id = null) {
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
function getCourseById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT c.*, u.full_name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.user_id WHERE c.course_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get enrolled courses for student
function getEnrolledCourses($student_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT c.*, u.full_name as instructor_name FROM enrollments e JOIN courses c ON e.course_id = c.course_id JOIN users u ON c.instructor_id = u.user_id WHERE e.student_id = ?");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if student is enrolled in course
function isEnrolled($student_id, $course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
    $stmt->execute([$student_id, $course_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
}

// Get modules for course
function getModules($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY sequence");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get lessons for module
function getLessons($module_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE module_id = ? ORDER BY sequence");
    $stmt->execute([$module_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get assignments for course
function getAssignments($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE course_id = ? ORDER BY created_at DESC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get announcements for course
function getAnnouncements($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, u.full_name as author_name FROM announcements a JOIN users u ON a.author_id = u.user_id WHERE a.course_id = ? ORDER BY created_at DESC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get discussions for course
function getDiscussions($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT d.*, u.full_name as user_name, u.profile_picture FROM discussions d JOIN users u ON d.user_id = u.user_id WHERE d.course_id = ? ORDER BY created_at DESC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get replies for discussion
function getDiscussionReplies($discussion_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT r.*, u.full_name as user_name, u.profile_picture FROM discussion_replies r JOIN users u ON r.user_id = u.user_id WHERE r.discussion_id = ? ORDER BY created_at ASC");
    $stmt->execute([$discussion_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get quizzes for course
function getQuizzes($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ? ORDER BY created_at DESC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get quiz attempts for user
function getQuizAttempts($quiz_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quiz_attempts WHERE quiz_id = ? AND user_id = ? ORDER BY started_at DESC");
    $stmt->execute([$quiz_id, $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Count students enrolled in course
function countEnrolledStudents($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
}

// Get course progress for student
function getCourseProgress($student_id, $course_id) {
    global $pdo;
    // Count total lessons in course
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM lessons l JOIN modules m ON l.module_id = m.module_id WHERE m.course_id = ?");
    $stmt->execute([$course_id]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Count completed lessons (you'll need to implement a completion tracking system)
    $completed = 0; // Placeholder
    
    return $total > 0 ? round(($completed / $total) * 100) : 0;
}
?>
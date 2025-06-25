<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('manage_students.php');
}

$studentId = $_GET['id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$instructorId = $_SESSION['user_id'];

// Get student data and verify enrollment in instructor's course
$query = "
    SELECT u.* 
    FROM users u
    JOIN enrollments e ON u.user_id = e.student_id
    JOIN courses c ON e.course_id = c.course_id
    WHERE u.user_id = $studentId AND c.instructor_id = $instructorId AND u.role = 'student'
";

if ($courseId > 0) {
    $query .= " AND e.course_id = $courseId";
}

$query .= " LIMIT 1";

$student = $db->query($query)->fetch_assoc();

if (!$student) {
    redirect('manage_students.php');
}

// Get courses the student is enrolled in (taught by this instructor)
$enrolledCourses = $db->query("
    SELECT c.course_id, c.title, e.enrolled_at
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.student_id = $studentId AND c.instructor_id = $instructorId AND e.status = 'active'
    ORDER BY e.enrolled_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Get assignments submitted by this student
$submittedAssignments = $db->query("
    SELECT a.assignment_id, a.title, c.title as course_title, s.grade, a.max_points
    FROM assignment_submissions s
    JOIN assignments a ON s.assignment_id = a.assignment_id
    JOIN courses c ON a.course_id = c.course_id
    WHERE s.student_id = $studentId AND c.instructor_id = $instructorId
    ORDER BY s.submitted_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get quiz attempts by this student
$quizAttempts = $db->query("
    SELECT a.attempt_id, q.title as quiz_title, c.title as course_title, a.score
    FROM quiz_attempts a
    JOIN quizzes q ON a.quiz_id = q.quiz_id
    JOIN courses c ON q.course_id = c.course_id
    WHERE a.student_id = $studentId AND c.instructor_id = $instructorId
    ORDER BY a.started_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'View Student';
require_once '../../includes/header.php';
?>

<div class="view-student">
    <h1>Student: <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h1>

    <div class="student-info">
        <div class="profile-picture">
            <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $student['profile_picture'] ?? 'default.png'; ?>" alt="Profile" width="150">
        </div>
        <div class="details">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($student['username']); ?></p>
            <p><strong>Joined:</strong> <?php echo formatDate($student['created_at']); ?></p>
        </div>
    </div>

    <div class="student-courses">
        <h2>Enrolled Courses</h2>
        <?php if (!empty($enrolledCourses)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Enrolled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrolledCourses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td><?php echo formatDate($course['enrolled_at']); ?></td>
                            <td>
                                <a href="../courses/view_course.php?id=<?php echo $course['course_id']; ?>" class="btn">View Course</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Student is not enrolled in any of your courses.</p>
        <?php endif; ?>
    </div>

    <div class="student-columns">
        <div class="column">
            <h2>Recent Assignments</h2>
            <?php if (!empty($submittedAssignments)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Assignment</th>
                            <th>Course</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submittedAssignments as $assignment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['course_title']); ?></td>
                                <td>
                                    <?php if ($assignment['grade'] !== null): ?>
                                        <?php echo $assignment['grade']; ?> / <?php echo $assignment['max_points']; ?>
                                    <?php else: ?>
                                        <span class="status pending">Not Graded</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="view-all">
                    <a href="../assignments/submissions/view_submissions.php?student_id=<?php echo $studentId; ?>" class="btn">View All</a>
                </div>
            <?php else: ?>
                <p>No submitted assignments found.</p>
            <?php endif; ?>
        </div>

        <div class="column">
            <h2>Recent Quiz Attempts</h2>
            <?php if (!empty($quizAttempts)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Quiz</th>
                            <th>Course</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quizAttempts as $attempt): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                                <td><?php echo htmlspecialchars($attempt['course_title']); ?></td>
                                <td>
                                    <?php if ($attempt['score'] !== null): ?>
                                        <?php echo $attempt['score']; ?>%
                                    <?php else: ?>
                                        <span class="status pending">Not Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="view-all">
                    <a href="../quizzes/results/quiz_results.php?student_id=<?php echo $studentId; ?>" class="btn">View All</a>
                </div>
            <?php else: ?>
                <p>No quiz attempts found.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="action-bar">
        <a href="manage_students.php<?php echo $courseId ? '?course_id=' . $courseId : ''; ?>" class="btn">Back to Students</a>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
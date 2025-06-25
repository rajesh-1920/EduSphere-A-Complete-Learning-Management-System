<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('../manage_assignments.php');
}

$assignmentId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Verify instructor owns this assignment
$assignment = $db->query("
    SELECT a.*, c.title as course_title
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    WHERE a.assignment_id = $assignmentId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$assignment) {
    redirect('../manage_assignments.php');
}

// Get submissions for this assignment
$submissions = $db->query("
    SELECT s.*, u.first_name, u.last_name, u.email
    FROM assignment_submissions s
    JOIN users u ON s.student_id = u.user_id
    WHERE s.assignment_id = $assignmentId
    ORDER BY s.submitted_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'View Submissions';
require_once '../../../includes/header.php';
?>

<div class="view-submissions">
    <h1>Submissions for: <?php echo htmlspecialchars($assignment['title']); ?></h1>
    <p>Course: <?php echo htmlspecialchars($assignment['course_title']); ?></p>

    <div class="action-bar">
        <a href="../view_assignment.php?id=<?php echo $assignmentId; ?>" class="btn">Back to Assignment</a>
    </div>

    <?php if (!empty($submissions)): ?>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Submitted</th>
                    <th>Grade</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']); ?><br>
                            <small><?php echo htmlspecialchars($submission['email']); ?></small>
                        </td>
                        <td><?php echo formatDate($submission['submitted_at']); ?></td>
                        <td>
                            <?php if ($submission['grade'] !== null): ?>
                                <?php echo $submission['grade']; ?> / <?php echo $assignment['max_points']; ?>
                            <?php else: ?>
                                <span class="status pending">Not Graded</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="grade_submission.php?id=<?php echo $submission['submission_id']; ?>" class="btn">Grade</a>
                            <a href="view_submission.php?id=<?php echo $submission['submission_id']; ?>" class="btn">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No submissions for this assignment yet.</p>
    <?php endif; ?>
</div>

<?php require_once '../../../includes/footer.php'; ?>
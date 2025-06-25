<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('manage_assignments.php');
}

$assignmentId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get assignment data and verify ownership
$assignment = $db->query("
    SELECT a.*, c.title as course_title, u.first_name, u.last_name
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    JOIN users u ON c.instructor_id = u.user_id
    WHERE a.assignment_id = $assignmentId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$assignment) {
    redirect('manage_assignments.php');
}

// Get submission count
$submissionCount = $db->query("
    SELECT COUNT(*) FROM assignment_submissions 
    WHERE assignment_id = $assignmentId
")->fetch_row()[0];

$pageTitle = 'View Assignment';
require_once '../../includes/header.php';
?>

<div class="view-assignment">
    <h1><?php echo htmlspecialchars($assignment['title']); ?></h1>
    <p>Course: <?php echo htmlspecialchars($assignment['course_title']); ?></p>
    <p>Instructor: <?php echo htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name']); ?></p>
    <p>Due Date: <?php echo formatDate($assignment['due_date']); ?></p>
    <p>Max Points: <?php echo $assignment['max_points']; ?></p>
    <p>Submissions: <?php echo $submissionCount; ?></p>

    <div class="assignment-content">
        <h3>Description</h3>
        <div class="content-box">
            <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
        </div>
    </div>

    <div class="action-bar">
        <a href="edit_assignment.php?id=<?php echo $assignmentId; ?>" class="btn">Edit</a>
        <a href="submissions/view_submissions.php?id=<?php echo $assignmentId; ?>" class="btn">View Submissions</a>
        <a href="manage_assignments.php" class="btn">Back to Assignments</a>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
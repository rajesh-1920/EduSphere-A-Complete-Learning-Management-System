<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('../../manage_assignments.php');
}

$submissionId = $_GET['id'];
$instructorId = $_SESSION['user_id'];

// Get submission data and verify ownership
$submission = $db->query("
    SELECT s.*, a.title as assignment_title, a.max_points, 
           u.first_name, u.last_name, u.email,
           c.title as course_title
    FROM assignment_submissions s
    JOIN assignments a ON s.assignment_id = a.assignment_id
    JOIN courses c ON a.course_id = c.course_id
    JOIN users u ON s.student_id = u.user_id
    WHERE s.submission_id = $submissionId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$submission) {
    redirect('../../manage_assignments.php');
}

$pageTitle = 'View Submission';
require_once '../../../includes/header.php';
?>

<div class="view-submission">
    <h1>Submission for: <?php echo htmlspecialchars($submission['assignment_title']); ?></h1>
    <p>Course: <?php echo htmlspecialchars($submission['course_title']); ?></p>
    <p>Student: <?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']); ?></p>
    <p>Email: <?php echo htmlspecialchars($submission['email']); ?></p>
    <p>Submitted: <?php echo formatDate($submission['submitted_at']); ?></p>
    
    <?php if ($submission['grade'] !== null): ?>
        <p>Grade: <?php echo $submission['grade']; ?> / <?php echo $submission['max_points']; ?></p>
        <?php if (!empty($submission['feedback'])): ?>
            <p>Feedback: <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?></p>
        <?php endif; ?>
    <?php else: ?>
        <p>Grade: <span class="status pending">Not Graded</span></p>
    <?php endif; ?>
    
    <div class="submission-content">
        <h3>Submission Text</h3>
        <div class="content-box">
            <?php echo nl2br(htmlspecialchars($submission['submission_text'])); ?>
        </div>
        
        <?php if (!empty($submission['file_path'])): ?>
            <h3>Attached File</h3>
            <div class="file-download">
                <a href="<?php echo SITE_URL; ?>/uploads/assignments/<?php echo $submission['file_path']; ?>" download class="btn">
                    Download File
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="action-bar">
        <a href="grade_submission.php?id=<?php echo $submissionId; ?>" class="btn">Grade Submission</a>
        <a href="view_submissions.php?id=<?php echo $submission['assignment_id']; ?>" class="btn">Back to Submissions</a>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>
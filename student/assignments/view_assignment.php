<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

if (!isset($_GET['id'])) {
    redirect('my_assignments.php');
}

$assignmentId = $_GET['id'];
$studentId = $_SESSION['user_id'];
$db = new Database();

// Get assignment details
$assignment = $db->query("
    SELECT a.*, c.title as course_title
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON a.course_id = e.course_id
    WHERE a.assignment_id = $assignmentId
    AND e.student_id = $studentId
    AND e.status = 'active'
")->fetch_assoc();

if (!$assignment) {
    redirect('my_assignments.php');
}

// Get submission if exists
$submission = $db->query("
    SELECT * FROM assignment_submissions
    WHERE assignment_id = $assignmentId
    AND student_id = $studentId
")->fetch_assoc();

$pageTitle = $assignment['title'];
require_once '../../includes/header.php';
?>

<div class="view-assignment">
    <div class="assignment-header">
        <h1><?php echo htmlspecialchars($assignment['title']); ?></h1>
        <p class="course">Course: <?php echo htmlspecialchars($assignment['course_title']); ?></p>
        <p class="due-date">Due: <?php echo formatDate($assignment['due_date']); ?></p>
        <p class="points">Points: <?php echo $assignment['max_points']; ?></p>
    </div>
    
    <div class="assignment-content">
        <h3>Description</h3>
        <div class="description">
            <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
        </div>
        
        <?php if (!empty($assignment['file_path'])): ?>
            <div class="resources">
                <h3>Resources</h3>
                <a href="<?php echo SITE_URL; ?>/uploads/assignments/<?php echo $assignment['file_path']; ?>" 
                   class="btn" download>Download Resources</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="submission-section">
        <h2>Your Submission</h2>
        
        <?php if ($submission): ?>
            <div class="submission-details">
                <p><strong>Submitted on:</strong> <?php echo formatDate($submission['submitted_at']); ?></p>
                
                <?php if (!empty($submission['submission_text'])): ?>
                    <div class="submission-text">
                        <h4>Submission Text</h4>
                        <?php echo nl2br(htmlspecialchars($submission['submission_text'])); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($submission['file_path'])): ?>
                    <div class="submission-file">
                        <h4>Submitted File</h4>
                        <a href="<?php echo SITE_URL; ?>/uploads/assignments/<?php echo $submission['file_path']; ?>" 
                           class="btn" download>Download Your Submission</a>
                    </div>
                <?php endif; ?>
                
                <?php if ($submission['grade'] !== null): ?>
                    <div class="grade-feedback">
                        <h4>Grade & Feedback</h4>
                        <p><strong>Grade:</strong> <?php echo $submission['grade']; ?>/<?php echo $assignment['max_points']; ?></p>
                        <?php if (!empty($submission['feedback'])): ?>
                            <div class="feedback">
                                <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p>Your submission is awaiting grading.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="no-submission">
                <?php if (strtotime($assignment['due_date']) < time()): ?>
                    <p class="alert alert-error">This assignment is past due.</p>
                <?php else: ?>
                    <p>You haven't submitted this assignment yet.</p>
                    <a href="submit_assignment.php?id=<?php echo $assignmentId; ?>" class="btn">Submit Assignment</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
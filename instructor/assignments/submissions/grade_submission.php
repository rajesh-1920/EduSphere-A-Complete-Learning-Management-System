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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade = isset($_POST['grade']) ? (int)$_POST['grade'] : null;
    $feedback = sanitize($_POST['feedback']);

    // Validate inputs
    if ($grade !== null && ($grade < 0 || $grade > $submission['max_points'])) {
        $errors[] = 'Grade must be between 0 and ' . $submission['max_points'];
    }

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE assignment_submissions SET grade = ?, feedback = ? WHERE submission_id = ?");
        $stmt->bind_param("isi", $grade, $feedback, $submissionId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Submission graded successfully!';
            redirect("view_submission.php?id=$submissionId");
        } else {
            $errors[] = 'Failed to grade submission. Please try again.';
        }
    }
}

$pageTitle = 'Grade Submission';
require_once '../../../includes/header.php';
?>

<div class="grade-submission">
    <h1>Grade Submission</h1>
    <p>Assignment: <?php echo htmlspecialchars($submission['assignment_title']); ?></p>
    <p>Course: <?php echo htmlspecialchars($submission['course_title']); ?></p>
    <p>Student: <?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']); ?></p>
    <p>Max Points: <?php echo $submission['max_points']; ?></p>
    
    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
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
    
    <form method="post">
        <div class="form-group">
            <label for="grade">Grade (out of <?php echo $submission['max_points']; ?>)</label>
            <input type="number" id="grade" name="grade" 
                   min="0" max="<?php echo $submission['max_points']; ?>" 
                   value="<?php echo $submission['grade'] !== null ? $submission['grade'] : ''; ?>">
        </div>
        <div class="form-group">
            <label for="feedback">Feedback</label>
            <textarea id="feedback" name="feedback" rows="6"><?php echo htmlspecialchars($submission['feedback'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn">Save Grade</button>
        <a href="view_submission.php?id=<?php echo $submissionId; ?>" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../../includes/footer.php'; ?>
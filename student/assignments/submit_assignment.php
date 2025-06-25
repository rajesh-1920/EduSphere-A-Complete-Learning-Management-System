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

// Verify assignment exists and is not past due
$assignment = $db->query("
    SELECT a.*, c.title as course_title
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON a.course_id = e.course_id
    WHERE a.assignment_id = $assignmentId
    AND e.student_id = $studentId
    AND e.status = 'active'
    AND a.due_date > NOW()
")->fetch_assoc();

if (!$assignment) {
    $_SESSION['error'] = "Assignment not found or submission deadline has passed.";
    redirect('my_assignments.php');
}

// Check if already submitted
$submission = $db->query("
    SELECT * FROM assignment_submissions
    WHERE assignment_id = $assignmentId
    AND student_id = $studentId
")->fetch_assoc();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submissionText = sanitize($_POST['submission_text'] ?? '');
    $file = $_FILES['submission_file'] ?? null;
    
    // Validate inputs
    if (empty($submissionText) && (!$file || $file['error'] === UPLOAD_ERR_NO_FILE)) {
        $errors[] = "Either submission text or a file is required.";
    }
    
    // Handle file upload
    $filePath = null;
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Only PDF and Word documents are allowed.";
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            $errors[] = "File size must be less than 5MB.";
        } else {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $extension;
            $uploadPath = ASSIGNMENT_UPLOAD_PATH . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $filePath = $fileName;
            } else {
                $errors[] = "Failed to upload file.";
            }
        }
    }
    
    if (empty($errors)) {
        if ($submission) {
            // Update existing submission
            $stmt = $db->prepare("
                UPDATE assignment_submissions SET
                submission_text = ?,
                file_path = ?,
                submitted_at = NOW()
                WHERE submission_id = ?
            ");
            $stmt->bind_param("ssi", $submissionText, $filePath, $submission['submission_id']);
        } else {
            // Create new submission
            $stmt = $db->prepare("
                INSERT INTO assignment_submissions (
                    assignment_id, 
                    student_id, 
                    submission_text, 
                    file_path
                ) VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iiss", $assignmentId, $studentId, $submissionText, $filePath);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Assignment submitted successfully!";
            redirect("view_assignment.php?id=$assignmentId");
        } else {
            $errors[] = "Failed to submit assignment. Please try again.";
        }
    }
}

$pageTitle = "Submit Assignment: " . htmlspecialchars($assignment['title']);
require_once '../../includes/header.php';
?>

<div class="submit-assignment">
    <h1>Submit Assignment: <?php echo htmlspecialchars($assignment['title']); ?></h1>
    <p class="course">Course: <?php echo htmlspecialchars($assignment['course_title']); ?></p>
    <p class="due-date">Due: <?php echo formatDate($assignment['due_date']); ?></p>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="submission_text" class="form-label">Submission Text</label>
            <textarea id="submission_text" name="submission_text" class="form-control" rows="10"><?php 
                echo htmlspecialchars($submission['submission_text'] ?? $_POST['submission_text'] ?? ''); 
            ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="submission_file" class="form-label">Or Upload File</label>
            <input type="file" id="submission_file" name="submission_file" class="form-control">
            <small class="form-text">PDF or Word document only (max 5MB)</small>
            
            <?php if (!empty($submission['file_path'])): ?>
                <div class="current-file">
                    <p>Current file: 
                        <a href="<?php echo SITE_URL; ?>/uploads/assignments/<?php echo $submission['file_path']; ?>" 
                           download><?php echo basename($submission['file_path']); ?></a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Submit Assignment</button>
            <a href="view_assignment.php?id=<?php echo $assignmentId; ?>" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
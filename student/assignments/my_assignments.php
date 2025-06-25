<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

$studentId = $_SESSION['user_id'];
$db = new Database();

// Get assignments with submission status
$assignments = $db->query("
    SELECT a.*, c.title as course_title, s.submission_id, s.grade,
           (SELECT COUNT(*) FROM assignment_submissions 
            WHERE assignment_id = a.assignment_id) as total_submissions
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON a.course_id = e.course_id
    LEFT JOIN assignment_submissions s ON a.assignment_id = s.assignment_id AND s.student_id = $studentId
    WHERE e.student_id = $studentId AND e.status = 'active'
    ORDER BY a.due_date ASC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'My Assignments';
require_once '../../includes/header.php';
?>

<div class="my-assignments">
    <h1>My Assignments</h1>
    
    <?php if (!empty($assignments)): ?>
        <table class="assignments-table">
            <thead>
                <tr>
                    <th>Assignment</th>
                    <th>Course</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Submissions</th>
                    <th>Grade</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['course_title']); ?></td>
                        <td><?php echo formatDate($assignment['due_date']); ?></td>
                        <td>
                            <?php if ($assignment['submission_id']): ?>
                                <span class="status submitted">Submitted</span>
                            <?php elseif (strtotime($assignment['due_date']) < time()): ?>
                                <span class="status overdue">Overdue</span>
                            <?php else: ?>
                                <span class="status pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $assignment['total_submissions']; ?></td>
                        <td>
                            <?php if ($assignment['grade'] !== null): ?>
                                <?php echo $assignment['grade']; ?>/<?php echo $assignment['max_points']; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="view_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn">View</a>
                            <?php if (!$assignment['submission_id'] && strtotime($assignment['due_date']) > time()): ?>
                                <a href="submit_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn">Submit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No assignments found for your enrolled courses.</p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
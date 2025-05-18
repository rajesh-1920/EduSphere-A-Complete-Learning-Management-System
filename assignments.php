<?php
require_once 'config.php';
requireLogin();
requireRole('student');

$userId = $_SESSION['user_id'];

// Get assignments with submission status
$assignments = $pdo->prepare("SELECT a.*, c.title as course_title, 
                             s.submission_id, s.submitted_at, s.score,
                             DATEDIFF(a.due_date, NOW()) as days_remaining
                             FROM assignments a
                             JOIN courses c ON a.course_id = c.course_id
                             JOIN enrollments e ON c.course_id = e.course_id
                             LEFT JOIN submissions s ON a.assignment_id = s.assignment_id 
                                 AND s.student_id = ?
                             WHERE e.student_id = ?
                             ORDER BY a.due_date ASC");
$assignments->execute([$userId, $userId]);
$assignments = $assignments->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "My Assignments";
require_once 'header.php';
?>

<div class="dashboard">
    <div class="sidebar">
        <h3 class="sidebar-title">My Learning</h3>
        <ul class="sidebar-menu">
            <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
            <li><a href="assignments.php" class="active"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="grades.php"><i class="fas fa-chart-bar"></i> Grades</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h2>My Assignments</h2>
        </div>
        
        <?php if (empty($assignments)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <h4>No assignments found</h4>
                    <p>You currently don't have any assignments in your enrolled courses</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Assignment</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment): 
                                $dueClass = $assignment['days_remaining'] < 0 ? 'text-danger' : 
                                           ($assignment['days_remaining'] < 3 ? 'text-warning' : 'text-success');
                                $status = $assignment['submission_id'] ? 'Submitted' : 'Pending';
                                $statusClass = $assignment['submission_id'] ? 'success' : 'warning';
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($assignment['course_title']) ?></td>
                                    <td><?= htmlspecialchars($assignment['title']) ?></td>
                                    <td class="<?= $dueClass ?>">
                                        <?= date('M j, Y', strtotime($assignment['due_date'])) ?>
                                        <small class="d-block">(<?= $assignment['days_remaining'] ?> days left)</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $statusClass ?>"><?= $status ?></span>
                                    </td>
                                    <td>
                                        <?= $assignment['score'] !== null ? $assignment['score'] . '/' . $assignment['max_score'] : '-' ?>
                                    </td>
                                    <td>
                                        <a href="assignment.php?id=<?= $assignment['assignment_id'] ?>" 
                                           class="btn btn-sm btn-primary">
                                            <?= $assignment['submission_id'] ? 'View' : 'Submit' ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
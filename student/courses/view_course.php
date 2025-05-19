<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

if (!isset($_GET['id'])) {
    redirect('my_courses.php');
}

$courseId = $_GET['id'];
$studentId = $_SESSION['user_id'];
$db = new Database();

// Verify student is enrolled in this course
$enrollment = $db->query("
    SELECT e.enrollment_id 
    FROM enrollments e
    WHERE e.student_id = $studentId AND e.course_id = $courseId AND e.status = 'active'
")->fetch_row();

if (!$enrollment) {
    redirect('my_courses.php');
}

// Get course data
$course = $db->query("
    SELECT c.*, u.first_name, u.last_name
    FROM courses c
    JOIN users u ON c.instructor_id = u.user_id
    WHERE c.course_id = $courseId
")->fetch_assoc();

// Get modules for this course
$modules = $db->query("
    SELECT * FROM modules 
    WHERE course_id = $courseId
    ORDER BY position ASC
")->fetch_all(MYSQLI_ASSOC);

// Get announcements for this course
$announcements = $db->query("
    SELECT * FROM announcements
    WHERE course_id = $courseId
    ORDER BY created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get assignments for this course
$assignments = $db->query("
    SELECT a.*, s.submission_id, s.grade
    FROM assignments a
    LEFT JOIN assignment_submissions s ON a.assignment_id = s.assignment_id AND s.student_id = $studentId
    WHERE a.course_id = $courseId
    ORDER BY a.due_date ASC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = $course['title'];
require_once '../../includes/header.php';
?>

<div class="view-course">
    <div class="course-header">
        <?php if ($course['thumbnail']): ?>
            <img src="<?php echo SITE_URL; ?>/uploads/course_thumbnails/<?php echo $course['thumbnail']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-thumbnail">
        <?php endif; ?>
        <div class="course-info">
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <p class="instructor">Instructor: <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></p>
            <p class="description"><?php echo htmlspecialchars($course['description']); ?></p>
        </div>
    </div>
    
    <div class="course-tabs">
        <button class="tab-btn active" data-tab="modules">Modules</button>
        <button class="tab-btn" data-tab="announcements">Announcements</button>
        <button class="tab-btn" data-tab="assignments">Assignments</button>
    </div>
    
    <div class="tab-content active" id="modules-tab">
        <?php if (!empty($modules)): ?>
            <div class="modules-list">
                <?php foreach ($modules as $module): ?>
                    <div class="module-card">
                        <h3><?php echo htmlspecialchars($module['title']); ?></h3>
                        <p><?php echo htmlspecialchars($module['description']); ?></p>
                        <a href="../modules/view_module.php?id=<?php echo $module['module_id']; ?>" class="btn">View Module</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No modules available yet.</p>
        <?php endif; ?>
    </div>
    
    <div class="tab-content" id="announcements-tab">
        <?php if (!empty($announcements)): ?>
            <div class="announcements-list">
                <?php foreach ($announcements as $announcement): ?>
                    <div class="announcement-card">
                        <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                        <p class="date"><?php echo formatDate($announcement['created_at']); ?></p>
                        <p class="content"><?php echo nl2br(htmlspecialchars(substr($announcement['content'], 0, 200))); ?>...</p>
                        <a href="../announcements/view_announcement.php?id=<?php echo $announcement['announcement_id']; ?>" class="btn">Read More</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all">
                <a href="../announcements/view_announcements.php?course_id=<?php echo $courseId; ?>" class="btn">View All Announcements</a>
            </div>
        <?php else: ?>
            <p>No announcements for this course yet.</p>
        <?php endif; ?>
    </div>
    
    <div class="tab-content" id="assignments-tab">
        <?php if (!empty($assignments)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Assignment</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Grade</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assignment['title']); ?></td>
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
                            <td>
                                <?php if ($assignment['grade'] !== null): ?>
                                    <?php echo $assignment['grade']; ?> / <?php echo $assignment['max_points']; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="../assignments/view_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn">View</a>
                                <?php if (!$assignment['submission_id'] && strtotime($assignment['due_date']) > time()): ?>
                                    <a href="../assignments/submit_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn">Submit</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No assignments for this course yet.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabBtns.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(tabId + '-tab').classList.add('active');
        });
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
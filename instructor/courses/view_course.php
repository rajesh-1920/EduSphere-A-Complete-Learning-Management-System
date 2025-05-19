<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

if (!isset($_GET['id'])) {
    redirect('manage_courses.php');
}

$courseId = $_GET['id'];
$instructorId = $_SESSION['user_id'];
$db = new Database();

// Get course data
$course = $db->query("
    SELECT c.*, u.first_name, u.last_name
    FROM courses c
    JOIN users u ON c.instructor_id = u.user_id
    WHERE c.course_id = $courseId AND c.instructor_id = $instructorId
")->fetch_assoc();

if (!$course) {
    redirect('manage_courses.php');
}

// Get modules for this course
$modules = $db->query("
    SELECT * FROM modules 
    WHERE course_id = $courseId
    ORDER BY position ASC
")->fetch_all(MYSQLI_ASSOC);

// Get students enrolled in this course
$students = $db->query("
    SELECT u.user_id, u.first_name, u.last_name, u.email, e.enrolled_at
    FROM enrollments e
    JOIN users u ON e.student_id = u.user_id
    WHERE e.course_id = $courseId AND e.status = 'active'
    ORDER BY e.enrolled_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Get recent announcements for this course
$announcements = $db->query("
    SELECT * FROM announcements
    WHERE course_id = $courseId
    ORDER BY created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get upcoming assignments for this course
$assignments = $db->query("
    SELECT * FROM assignments
    WHERE course_id = $courseId AND due_date > NOW()
    ORDER BY due_date ASC
    LIMIT 5
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
        <button class="tab-btn" data-tab="students">Students (<?php echo count($students); ?>)</button>
        <button class="tab-btn" data-tab="announcements">Announcements</button>
        <button class="tab-btn" data-tab="assignments">Assignments</button>
    </div>
    
    <div class="tab-content active" id="modules-tab">
        <div class="action-bar">
            <a href="modules/add_module.php?course_id=<?php echo $courseId; ?>" class="btn">Add Module</a>
        </div>
        
        <?php if (!empty($modules)): ?>
            <div class="modules-list">
                <?php foreach ($modules as $module): ?>
                    <div class="module-card">
                        <h3><?php echo htmlspecialchars($module['title']); ?></h3>
                        <p><?php echo htmlspecialchars($module['description']); ?></p>
                        <div class="module-actions">
                            <a href="modules/view_module.php?id=<?php echo $module['module_id']; ?>" class="btn">View</a>
                            <a href="modules/edit_module.php?id=<?php echo $module['module_id']; ?>" class="btn">Edit</a>
                            <a href="modules/delete_module.php?id=<?php echo $module['module_id']; ?>" class="btn delete-btn">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No modules added yet. <a href="modules/add_module.php?course_id=<?php echo $courseId; ?>">Add your first module</a></p>
        <?php endif; ?>
    </div>
    
    <div class="tab-content" id="students-tab">
        <?php if (!empty($students)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Enrolled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo formatDate($student['enrolled_at']); ?></td>
                            <td>
                                <a href="../students/view_student.php?id=<?php echo $student['user_id']; ?>&course_id=<?php echo $courseId; ?>" class="btn">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No students enrolled in this course yet.</p>
        <?php endif; ?>
    </div>
    
    <div class="tab-content" id="announcements-tab">
        <div class="action-bar">
            <a href="../announcements/add_announcement.php?course_id=<?php echo $courseId; ?>" class="btn">Add Announcement</a>
        </div>
        
        <?php if (!empty($announcements)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($announcements as $announcement): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                            <td><?php echo formatDate($announcement['created_at']); ?></td>
                            <td>
                                <a href="../announcements/edit_announcement.php?id=<?php echo $announcement['announcement_id']; ?>" class="btn">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No announcements for this course yet. <a href="../announcements/add_announcement.php?course_id=<?php echo $courseId; ?>">Create your first announcement</a></p>
        <?php endif; ?>
    </div>
    
    <div class="tab-content" id="assignments-tab">
        <div class="action-bar">
            <a href="../assignments/add_assignment.php?course_id=<?php echo $courseId; ?>" class="btn">Add Assignment</a>
        </div>
        
        <?php if (!empty($assignments)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                            <td><?php echo formatDate($assignment['due_date']); ?></td>
                            <td>
                                <a href="../assignments/view_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn">View</a>
                                <a href="../assignments/edit_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No upcoming assignments for this course yet. <a href="../assignments/add_assignment.php?course_id=<?php echo $courseId; ?>">Create your first assignment</a></p>
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
<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['instructor']);

$instructorId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Get quizzes for the instructor's courses
$query = "
    SELECT q.*, c.title as course_title
    FROM quizzes q
    JOIN courses c ON q.course_id = c.course_id
    WHERE c.instructor_id = $instructorId
";

if ($courseId > 0) {
    $query .= " AND q.course_id = $courseId";
}

$query .= " ORDER BY q.available_from ASC";

$quizzes = $db->query($query)->fetch_all(MYSQLI_ASSOC);

// Get instructor's courses for filter dropdown
$courses = $db->query("
    SELECT course_id, title FROM courses 
    WHERE instructor_id = $instructorId
    ORDER BY title
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Quizzes';
require_once '../../includes/header.php';
?>

<div class="manage-quizzes">
    <h1>Manage Quizzes</h1>
    
    <div class="action-bar">
        <a href="add_quiz.php" class="btn">Add Quiz</a>
        
        <form method="get" class="filter-form">
            <select name="course_id" onchange="this.form.submit()">
                <option value="">All Courses</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['course_id']; ?>" <?php echo $courseId == $course['course_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    
    <?php if (!empty($quizzes)): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Available From</th>
                    <th>Available To</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['course_title']); ?></td>
                        <td><?php echo formatDate($quiz['available_from']); ?></td>
                        <td><?php echo formatDate($quiz['available_to']); ?></td>
                        <td>
                            <a href="view_quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn">View</a>
                            <a href="edit_quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn">Edit</a>
                            <a href="questions/manage_questions.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn">Questions</a>
                            <a href="results/quiz_results.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn">Results</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No quizzes found. <a href="add_quiz.php">Create your first quiz</a></p>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
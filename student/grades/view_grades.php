<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['student']);

$studentId = $_SESSION['user_id'];
$db = new Database();

// Get all enrolled courses with grade information
$courses = $db->query("
    SELECT c.course_id, c.title, c.description, c.thumbnail,
           (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.course_id) as module_count,
           (SELECT COUNT(*) FROM announcements a WHERE a.course_id = c.course_id) as announcement_count
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.student_id = $studentId AND e.status = 'active'
    ORDER BY c.title
")->fetch_all(MYSQLI_ASSOC);

// Get all assignments with grades
$assignments = $db->query("
    SELECT a.assignment_id, a.title, a.max_points, a.course_id, 
           c.title as course_title, s.grade, s.feedback,
           (s.grade/a.max_points)*100 as percentage
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON a.course_id = c.course_id
    LEFT JOIN assignment_submissions s ON a.assignment_id = s.assignment_id AND s.student_id = $studentId
    WHERE e.student_id = $studentId AND e.status = 'active'
    AND s.grade IS NOT NULL
    ORDER BY a.due_date DESC
")->fetch_all(MYSQLI_ASSOC);

// Get all quiz attempts with grades
$quizzes = $db->query("
    SELECT q.quiz_id, q.title, q.course_id, c.title as course_title,
           a.attempt_id, a.score, a.completed_at
    FROM quizzes q
    JOIN courses c ON q.course_id = c.course_id
    JOIN enrollments e ON q.course_id = c.course_id
    JOIN quiz_attempts a ON q.quiz_id = a.quiz_id
    WHERE e.student_id = $studentId AND e.status = 'active'
    AND a.student_id = $studentId
    AND a.completed_at IS NOT NULL
    ORDER BY a.completed_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Calculate course averages
$courseGrades = [];
foreach ($courses as $course) {
    $courseGrades[$course['course_id']] = [
        'title' => $course['title'],
        'assignments' => [],
        'quizzes' => [],
        'total_points' => 0,
        'max_points' => 0,
        'average' => 0
    ];
}

foreach ($assignments as $assignment) {
    if (isset($courseGrades[$assignment['course_id']])) {
        $courseGrades[$assignment['course_id']]['assignments'][] = $assignment;
        $courseGrades[$assignment['course_id']]['total_points'] += $assignment['grade'];
        $courseGrades[$assignment['course_id']]['max_points'] += $assignment['max_points'];
    }
}

foreach ($quizzes as $quiz) {
    if (isset($courseGrades[$quiz['course_id']])) {
        $courseGrades[$quiz['course_id']]['quizzes'][] = $quiz;
        // Assuming quizzes are also out of 100 points for this calculation
        $courseGrades[$quiz['course_id']]['total_points'] += $quiz['score'];
        $courseGrades[$quiz['course_id']]['max_points'] += 100;
    }
}

// Calculate averages
foreach ($courseGrades as $courseId => $data) {
    if ($data['max_points'] > 0) {
        $courseGrades[$courseId]['average'] = round(($data['total_points'] / $data['max_points']) * 100, 2);
    }
}

$pageTitle = 'My Grades';
require_once '../../includes/header.php';
?>

<div class="grades-view">
    <h1>My Grades</h1>

    <div class="grades-summary">
        <h2>Course Overview</h2>
        <?php if (!empty($courseGrades)): ?>
            <div class="course-grades-grid">
                <?php foreach ($courseGrades as $courseId => $course): ?>
                    <div class="course-grade-card">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <div class="grade-display">
                            <?php if ($course['max_points'] > 0): ?>
                                <div class="grade-percentage">
                                    <div class="percentage-circle" data-percent="<?php echo $course['average']; ?>">
                                        <svg class="circle-chart" viewBox="0 0 36 36">
                                            <path class="circle-bg"
                                                d="M18 2.0845
                                                  a 15.9155 15.9155 0 0 1 0 31.831
                                                  a 15.9155 15.9155 0 0 1 0 -31.831"
                                            />
                                            <path class="circle-fill"
                                                stroke-dasharray="<?php echo $course['average']; ?>, 100"
                                                d="M18 2.0845
                                                  a 15.9155 15.9155 0 0 1 0 31.831
                                                  a 15.9155 15.9155 0 0 1 0 -31.831"
                                            />
                                            <text x="18" y="20.35" class="percentage-text"><?php echo $course['average']; ?>%</text>
                                        </svg>
                                </div>
                                <div class="grade-details">
                                    <p><strong>Assignments:</strong> <?php echo count($course['assignments']); ?> graded</p>
                                    <p><strong>Quizzes:</strong> <?php echo count($course['quizzes']); ?> graded</p>
                                    <p><strong>Total Points:</strong> <?php echo $course['total_points']; ?>/<?php echo $course['max_points']; ?></p>
                                </div>
                            <?php else: ?>
                                <p class="no-grades">No grades recorded yet for this course</p>
                            <?php endif; ?>
                        </div>
                        <a href="#course-<?php echo $courseId; ?>" class="btn btn-sm">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>You are not enrolled in any courses or no grades have been recorded yet.</p>
        <?php endif; ?>
    </div>

    <div class="grades-details">
        <h2>Detailed Grades</h2>
        
        <?php foreach ($courseGrades as $courseId => $course): ?>
            <div class="course-details" id="course-<?php echo $courseId; ?>">
                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                
                <?php if (!empty($course['assignments']) || !empty($course['quizzes'])): ?>
                    <div class="grade-tabs">
                        <button class="tab-btn active" data-tab="assignments-<?php echo $courseId; ?>">Assignments</button>
                        <button class="tab-btn" data-tab="quizzes-<?php echo $courseId; ?>">Quizzes</button>
                    </div>
                    
                    <div class="tab-content active" id="assignments-<?php echo $courseId; ?>">
                        <?php if (!empty($course['assignments'])): ?>
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <th>Assignment</th>
                                        <th>Grade</th>
                                        <th>Percentage</th>
                                        <th>Feedback</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($course['assignments'] as $assignment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                            <td><?php echo $assignment['grade']; ?>/<?php echo $assignment['max_points']; ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" 
                                                         style="width: <?php echo $assignment['percentage']; ?>%"
                                                         role="progressbar" 
                                                         aria-valuenow="<?php echo $assignment['percentage']; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <?php echo round($assignment['percentage']); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($assignment['feedback'])): ?>
                                                    <button class="btn-feedback" data-feedback="<?php echo htmlspecialchars($assignment['feedback']); ?>">
                                                        View Feedback
                                                    </button>
                                                <?php else: ?>
                                                    No feedback
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="../assignments/view_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No graded assignments for this course yet.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tab-content" id="quizzes-<?php echo $courseId; ?>">
                        <?php if (!empty($course['quizzes'])): ?>
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <th>Quiz</th>
                                        <th>Score</th>
                                        <th>Completed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($course['quizzes'] as $quiz): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                            <td><?php echo $quiz['score']; ?>%</td>
                                            <td><?php echo formatDate($quiz['completed_at']); ?></td>
                                            <td>
                                                <a href="../quizzes/quiz_results.php?attempt_id=<?php echo $quiz['attempt_id']; ?>" class="btn btn-sm">View Results</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No graded quizzes for this course yet.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p>No grades recorded yet for this course.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Instructor Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="feedbackContent">
                <!-- Feedback content will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle grade tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            const tabContainer = this.closest('.course-details');
            
            // Remove active class from all buttons and contents in this container
            tabContainer.querySelectorAll('.tab-btn').forEach(tb => tb.classList.remove('active'));
            tabContainer.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Handle feedback modal
    const feedbackBtns = document.querySelectorAll('.btn-feedback');
    feedbackBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const feedback = this.getAttribute('data-feedback');
            document.getElementById('feedbackContent').innerHTML = '<p>' + feedback + '</p>';
            var modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
            modal.show();
        });
    });
    
    // Animate percentage circles
    const circles = document.querySelectorAll('.percentage-circle');
    circles.forEach(circle => {
        const percent = circle.getAttribute('data-percent');
        const fill = circle.querySelector('.circle-fill');
        fill.style.strokeDasharray = percent + ', 100';
    });
});
</script>

<style>
.grades-view {
    padding: 20px;
}

.course-grades-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.course-grade-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.grade-display {
    display: flex;
    align-items: center;
    gap: 20px;
    margin: 15px 0;
}

.percentage-circle {
    width: 100px;
    height: 100px;
}

.circle-chart {
    width: 100%;
    height: 100%;
}

.circle-bg {
    fill: none;
    stroke: #eee;
    stroke-width: 3;
}

.circle-fill {
    fill: none;
    stroke: #4CAF50;
    stroke-width: 3;
    stroke-linecap: round;
    animation: circle-fill-animation 1.5s ease-in-out forwards;
}

.percentage-text {
    font-size: 0.5em;
    text-anchor: middle;
    fill: #333;
    font-weight: bold;
}

.grade-details {
    flex: 1;
}

.grade-details p {
    margin: 5px 0;
    font-size: 0.9em;
}

.no-grades {
    color: #666;
    font-style: italic;
}

.grade-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 15px;
}

.tab-btn {
    padding: 10px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1em;
    border-bottom: 3px solid transparent;
}

.tab-btn.active {
    border-bottom-color: #3498db;
    font-weight: bold;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.grades-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.grades-table th, .grades-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.grades-table th {
    background-color: #f8f9fa;
}

.progress {
    height: 20px;
    background-color: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background-color: #4CAF50;
    color: white;
    font-size: 0.75em;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-feedback {
    background: none;
    border: none;
    color: #3498db;
    cursor: pointer;
    padding: 0;
    text-decoration: underline;
}

.btn-feedback:hover {
    color: #2874a6;
}

@keyframes circle-fill-animation {
    0% {
        stroke-dasharray: 0, 100;
    }
}
</style>

<?php require_once '../../includes/footer.php'; ?>
<?php
require_once 'config.php';
requireLogin();
requireRole('student');

$userId = $_SESSION['user_id'];

// Get grades for all enrolled courses
$grades = $pdo->prepare("SELECT c.course_id, c.title as course_title, c.thumbnail,
                        COUNT(a.assignment_id) as total_assignments,
                        COUNT(s.submission_id) as submitted_assignments,
                        AVG(s.score) as average_score,
                        ar.final_grade as attendance_grade
                        FROM enrollments e
                        JOIN courses c ON e.course_id = c.course_id
                        LEFT JOIN assignments a ON c.course_id = a.course_id
                        LEFT JOIN submissions s ON a.assignment_id = s.assignment_id 
                            AND s.student_id = ?
                        LEFT JOIN attendance_results ar ON ar.course_id = c.course_id 
                            AND ar.student_id = ?
                        WHERE e.student_id = ?
                        GROUP BY c.course_id");
$grades->execute([$userId, $userId, $userId]);
$grades = $grades->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "My Grades";
require_once 'header.php';
?>

<div class="dashboard">
    <div class="sidebar">
        <h3 class="sidebar-title">My Learning</h3>
        <ul class="sidebar-menu">
            <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
            <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="grades.php" class="active"><i class="fas fa-chart-bar"></i> Grades</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h2>My Grades</h2>
        </div>
        
        <?php if (empty($grades)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <h4>No grade information available</h4>
                    <p>Your grades will appear here once you complete assignments</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Assignments</th>
                                <th>Average Score</th>
                                <th>Attendance Grade</th>
                                <th>Overall</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $course): 
                                $completion = $course['total_assignments'] > 0 ? 
                                    round(($course['submitted_assignments'] / $course['total_assignments']) * 100) : 0;
                                $average = $course['average_score'] ? round($course['average_score'], 1) : '-';
                                $overall = $course['average_score'] ? 
                                    round(($course['average_score'] + $course['attendance_grade']) / 2) : 
                                    $course['attendance_grade'];
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= !empty($course['thumbnail']) ? 
                                                      UPLOAD_DIR . 'courses/' . $course['thumbnail'] : 
                                                      'assets/course_default.png' ?>" 
                                                 width="40" height="40" class="rounded mr-3">
                                            <?= htmlspecialchars($course['course_title']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress-container">
                                            <div class="d-flex justify-content-between">
                                                <small><?= $course['submitted_assignments'] ?>/<?= $course['total_assignments'] ?></small>
                                                <small><?= $completion ?>%</small>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: <?= $completion ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $average ?></td>
                                    <td><?= $course['attendance_grade'] ?>%</td>
                                    <td>
                                        <strong><?= $overall ?>%</strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="mb-4">Grade Distribution</h4>
                    <canvas id="gradesChart" height="150"></canvas>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('gradesChart').getContext('2d');
    const gradesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($grades, 'course_title')) ?>,
            datasets: [{
                label: 'Assignment Average',
                data: <?= json_encode(array_map(function($g) { 
                    return $g['average_score'] ? round($g['average_score'], 1) : 0; 
                }, $grades)) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Attendance Grade',
                data: <?= json_encode(array_column($grades, 'attendance_grade')) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }, {
                label: 'Overall Grade',
                data: <?= json_encode(array_map(function($g) { 
                    return $g['average_score'] ? 
                        round(($g['average_score'] + $g['attendance_grade']) / 2) : 
                        $g['attendance_grade']; 
                }, $grades)) ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.5)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
});
</script>

<?php require_once 'footer.php'; ?>
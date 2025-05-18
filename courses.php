<?php
require_once 'config.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Build query to get courses
$query = "SELECT c.*, u.full_name as instructor_name, 
          COUNT(e.enrollment_id) as enrolled_students
          FROM courses c
          JOIN users u ON c.instructor_id = u.user_id
          LEFT JOIN enrollments e ON c.course_id = e.course_id
          WHERE 1=1";

$params = [];

// Add search condition
if (!empty($search)) {
    $query .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Add category filter
if (!empty($category) && $category != 'all') {
    $query .= " AND c.category = ?";
    $params[] = $category;
}

// Group by course
$query .= " GROUP BY c.course_id";

// Add sorting
switch ($sort) {
    case 'popular':
        $query .= " ORDER BY enrolled_students DESC";
        break;
    case 'rating':
        // You would add rating functionality separately
        $query .= " ORDER BY c.rating DESC";
        break;
    case 'title':
        $query .= " ORDER BY c.title ASC";
        break;
    default: // newest
        $query .= " ORDER BY c.created_at DESC";
        break;
}

// Get all courses
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories for filter
$categories = $pdo->query("SELECT DISTINCT category FROM courses WHERE category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = "Browse Courses";
require_once 'header.php';
?>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Browse Courses</h1>
            <p class="lead">Find the perfect course to enhance your skills</p>
        </div>
        <div class="col-md-4">
            <?php if (isLoggedIn() && hasRole('instructor')): ?>
                <a href="create_course.php" class="btn btn-primary float-right">
                    <i class="fas fa-plus"></i> Create Course
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="courses.php">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search courses..." value="<?= htmlspecialchars($search) ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="category" class="form-control">
                                <option value="all">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" 
                                        <?= $category == $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="sort" class="form-control">
                                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                                <option value="popular" <?= $sort == 'popular' ? 'selected' : '' ?>>Most Popular</option>
                                <option value="rating" <?= $sort == 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                                <option value="title" <?= $sort == 'title' ? 'selected' : '' ?>>A-Z</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Courses Grid -->
    <?php if (empty($courses)): ?>
        <div class="card">
            <div class="card-body text-center">
                <h4>No courses found</h4>
                <p>Try adjusting your search or filter criteria</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($courses as $course): 
                $isEnrolled = isLoggedIn() && isEnrolled($_SESSION['user_id'], $course['course_id']);
            ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 course-card">
                        <img src="<?= !empty($course['thumbnail']) ? 
                                  UPLOAD_DIR . 'courses/' . $course['thumbnail'] : 
                                  'assets/course_default.png' ?>" 
                             class="card-img-top" alt="<?= htmlspecialchars($course['title']) ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                                <span class="badge badge-secondary"><?= htmlspecialchars($course['category']) ?></span>
                            </div>
                            <p class="card-text text-muted">
                                <?= substr(htmlspecialchars($course['description']), 0, 100) ?>
                                <?= strlen($course['description']) > 100 ? '...' : '' ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> <?= $course['instructor_name'] ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-users"></i> <?= $course['enrolled_students'] ?> students
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <?php if (isLoggedIn()): ?>
                                    <?php if ($isEnrolled): ?>
                                        <a href="course.php?id=<?= $course['course_id'] ?>" class="btn btn-success btn-sm">
                                            Continue Learning
                                        </a>
                                    <?php else: ?>
                                        <a href="course_details.php?id=<?= $course['course_id'] ?>" class="btn btn-primary btn-sm">
                                            View Course
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php?redirect=courses.php" class="btn btn-primary btn-sm">
                                        View Course
                                    </a>
                                <?php endif; ?>
                                <span class="text-primary font-weight-bold">$99.99</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination would go here in a real implementation -->
</div>

<?php require_once 'footer.php'; ?>
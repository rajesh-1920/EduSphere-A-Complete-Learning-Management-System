<?php
require_once 'config.php';
requireRole('instructor');

$errors = [];
$title = $category = $description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $description = sanitize($_POST['description']);
    $instructor_id = $_SESSION['user_id'];
    
    // Validate inputs
    if (empty($title)) {
        $errors['title'] = 'Course title is required.';
    }
    
    if (empty($category)) {
        $errors['category'] = 'Category is required.';
    }
    
    if (empty($description)) {
        $errors['description'] = 'Description is required.';
    }
    
    // Handle file upload
    $thumbnail = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['thumbnail']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $thumbnail = uploadFile($_FILES['thumbnail'], 'courses/');
            if (!$thumbnail) {
                $errors['thumbnail'] = 'Failed to upload thumbnail.';
            }
        } else {
            $errors['thumbnail'] = 'Only JPG, PNG, and GIF files are allowed.';
        }
    } else {
        $errors['thumbnail'] = 'Thumbnail is required.';
    }
    
    // If no errors, create course
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO courses (title, description, instructor_id, category, thumbnail) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $instructor_id, $category, $thumbnail]);
            
            $courseId = $pdo->lastInsertId();
            $_SESSION['success_message'] = 'Course created successfully!';
            header("Location: instructor_course.php?id=$courseId");
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Failed to create course. Please try again.';
        }
    }
}

$pageTitle = "Create New Course";
require_once 'header.php';
?>

<div style="max-width: 800px; margin: 30px auto;">
    <div class="card">
        <div class="card-body">
            <h2 class="text-center" style="margin-bottom: 30px;">Create New Course</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin-bottom: 0;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="create_course.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title" class="form-label">Course Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" required>
                    <?php if (isset($errors['title'])): ?>
                        <small class="text-danger"><?php echo $errors['title']; ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">Select a category</option>
                        <option value="Web Development" <?php echo $category === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                        <option value="Mobile Development" <?php echo $category === 'Mobile Development' ? 'selected' : ''; ?>>Mobile Development</option>
                        <option value="Data Science" <?php echo $category === 'Data Science' ? 'selected' : ''; ?>>Data Science</option>
                        <option value="Business" <?php echo $category === 'Business' ? 'selected' : ''; ?>>Business</option>
                        <option value="Design" <?php echo $category === 'Design' ? 'selected' : ''; ?>>Design</option>
                        <option value="Marketing" <?php echo $category === 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                    </select>
                    <?php if (isset($errors['category'])): ?>
                        <small class="text-danger"><?php echo $errors['category']; ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Course Description</label>
                    <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($description); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <small class="text-danger"><?php echo $errors['description']; ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="thumbnail" class="form-label">Course Thumbnail</label>
                    <div style="border: 2px dashed #ddd; padding: 20px; text-align: center; border-radius: var(--border-radius); margin-bottom: 15px;">
                        <input type="file" id="thumbnail" name="thumbnail" class="file-input" style="display: none;" required>
                        <label for="thumbnail" style="cursor: pointer;">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 40px; color: var(--primary-color); margin-bottom: 10px;"></i>
                            <p>Click to upload thumbnail image</p>
                            <p class="text-muted">Recommended size: 1280x720 pixels</p>
                            <div id="thumbnail-preview" class="text-muted">No file chosen</div>
                        </label>
                    </div>
                    <?php if (isset($errors['thumbnail'])): ?>
                        <small class="text-danger"><?php echo $errors['thumbnail']; ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Create Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// File input preview
document.getElementById('thumbnail').addEventListener('change', function() {
    const preview = document.getElementById('thumbnail-preview');
    if (this.files.length > 0) {
        preview.textContent = this.files[0].name;
        preview.classList.remove('text-muted');
        preview.classList.add('text-success');
    } else {
        preview.textContent = 'No file chosen';
        preview.classList.add('text-muted');
        preview.classList.remove('text-success');
    }
});
</script>

<?php
require_once 'footer.php';
?>
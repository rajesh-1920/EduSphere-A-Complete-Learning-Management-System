<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Course Management</title>
    <link rel="stylesheet" href="css/course-management.css">
    <link rel="stylesheet" href="template/css/navbar.css">
</head>

<body>
    <!-- Navbar & Sidebar -->
    <?php
    include_once("template/navbar.php");
    ?>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Hero Section -->
        <div class="hero-content">
            <header>
                <h2>Course Management</h2>
                <p>Add, edit, or delete courses</p>
            </header>

            <!-- Add Course Form -->
            <section class="course-form">
                <h3>Add New Course</h3>
                <form action="add_course.php" method="POST">
                    <input type="text" name="course_title" placeholder="Course Title" required>
                    <textarea name="course_description" placeholder="Course Description" required></textarea>
                    <input type="text" name="course_price" placeholder="Price (Optional)">
                    <input type="file" name="course_image" accept="image/*">
                    <button type="submit" class="btn">Add Course</button>
                </form>
            </section>

            <!-- Course List -->
            <section class="course-list">
                <h3>Existing Courses</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sample Course</td>
                            <td>Introduction to Web Development</td>
                            <td>Free</td>
                            <td>
                                <button class="btn edit">Edit</button>
                                <button class="btn delete">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
        <!-- Footer -->
        <?php
        include_once("template/footer.php");
        ?>
    </div>
</body>

</html>
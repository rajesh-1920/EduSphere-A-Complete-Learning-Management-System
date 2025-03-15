<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Course Management</title>
    <link rel="stylesheet" href="css/course-management.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>LMS Dashboard</h2>
        <ul>
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="#"><i class="fas fa-book"></i> My Courses</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> Progress</a></li>
            <li><a href="#"><i class="fas fa-certificate"></i> Certificates</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
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
</body>

</html>
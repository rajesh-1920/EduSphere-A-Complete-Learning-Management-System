<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Home</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="template/css/navbar.css">
</head>

<body>
    <?php
    include_once("template/navbar.php");
    ?>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Hero Section -->
        <header class="hero">
            <div class="container">
                <h2 style=" color : black">Learn from the Best Courses Online</h2>
                <p style=" color : black">Join thousands of students on our learning platform.</p>
                <a href="#courses" class="btn">Browse Courses</a>
            </div>
        </header>

        <!-- Featured Courses Section -->
        <section id="courses" class="courses">
            <div class="container">
                <h2>Featured Courses</h2>
                <div class="course-grid">
                    <div class="course-card">
                        <img src="course1.jpg" alt="Course 1">
                        <h3>Web Development</h3>
                        <p>Learn HTML, CSS, JavaScript, and more.</p>
                        <a href="#" class="btn">Enroll Now</a>
                    </div>
                    <div class="course-card">
                        <img src="course2.jpg" alt="Course 2">
                        <h3>Python for Beginners</h3>
                        <p>Start coding in Python with this beginner-friendly course.</p>
                        <a href="#" class="btn">Enroll Now</a>
                    </div>
                    <div class="course-card">
                        <img src="course3.jpg" alt="Course 3">
                        <h3>Data Science</h3>
                        <p>Master data analysis and machine learning techniques.</p>
                        <a href="#" class="btn">Enroll Now</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <p>&copy; 2025 LMS. All rights reserved.</p>
            </div>
        </footer>
    </div>

</body>

</html>
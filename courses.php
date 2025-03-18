<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
    <link rel="stylesheet" href="css/courses.css">
    <link rel="stylesheet" href="template/css/navbar.css">
</head>

<body>
    <?php
    include_once("template/navbar.php");
    ?>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Hero Section -->
        <header>
            <h2>Available Courses</h2>
            <p>Browse and enroll in courses</p>
        </header>
        <section class="course-list">
            <div class="course">
                <img src="course1.jpg" alt="Course Image">
                <h3>Web Development</h3>
                <p>Learn HTML, CSS, and JavaScript from scratch.</p>
                <p><strong>Price:</strong> $50</p>
                <button class="enroll-btn">Enroll Now</button>
            </div>
            <div class="course">
                <img src="course2.jpg" alt="Course Image">
                <h3>PHP for Beginners</h3>
                <p>Master the basics of PHP development.</p>
                <p><strong>Price:</strong> $50</p>
                <button>Enroll Now</button>
            </div>
            <div class="course">
                <img src="course3.jpg" alt="Course Image">
                <h3>JavaScript Essentials</h3>
                <p>Learn JavaScript fundamentals and beyond.</p>
                <p><strong>Price:</strong> $50</p>
                <button>Enroll Now</button>
            </div>
        </section>
    </div>
</body>

</html>
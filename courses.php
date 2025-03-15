<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
    <link rel="stylesheet" href="css/courses.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="#"><i class="fas fa-book"></i> My Courses</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> Progress</a></li>
            <li><a href="#"><i class="fas fa-certificate"></i> Certificates</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
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
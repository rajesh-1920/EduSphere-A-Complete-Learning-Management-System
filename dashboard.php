<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
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
                <h2>Welcome, User</h2>
                <p>Manage your courses and progress here.</p>
            </header>

            <section class="dashboard-cards">
                <div class="card">
                    <h3>Enrolled Courses</h3>
                    <p>5</p>
                </div>
                <div class="card">
                    <h3>Completed Assignments</h3>
                    <p>8</p>
                </div>
                <div class="card">
                    <h3>Certificates Earned</h3>
                    <p>2</p>
                </div>
            </section>
        </div>
        <!-- Footer -->
        <?php
        include_once("template/footer.php");
        ?>
    </div>
</body>

</html>
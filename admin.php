<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="css/admin.css">
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
        <div class="profile-container">
            <h2>Admin Profile</h2>
            <img src="profile.jpg" alt="Admin Profile Picture" class="profile-pic">
            <h3>John Doe</h3>
            <p>Email: johndoe@example.com</p>
            <p>Enrolled Courses: 5</p>
            <p>Completed Courses: 2</p>
            <button class="btn">Edit Profile</button>
        </div>
        <!-- Footer -->
        <?php
        include_once("template/footer.php");
        ?>
    </div>
</body>

</html>
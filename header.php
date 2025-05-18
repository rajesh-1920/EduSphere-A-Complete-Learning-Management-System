<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">Edu<span>Sphere</span></a>

                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="courses.php">Courses</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('instructor')): ?>
                            <li><a href="instructor_dashboard.php">Instructor Dashboard</a></li>
                        <?php elseif (hasRole('student')): ?>
                            <li><a href="student_dashboard.php">My Learning</a></li>
                        <?php endif; ?>
                        <?php if (hasRole('admin')): ?>
                            <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>

                <div class="user-menu">
                    <?php if (isLoggedIn()): ?>
                        <a href="profile.php" class="btn btn-outline">Profile</a>
                        <a href="logout.php" class="btn btn-danger">Logout</a>
                        <?php
                        $user = getUserById($_SESSION['user_id']);
                        if ($user && !empty($user['profile_picture'])):
                        ?>
                            <img src="<?php echo UPLOAD_DIR . 'profile/' . $user['profile_picture']; ?>" alt="Profile Picture">
                        <?php else: ?>
                            <img src="assets/default.png" alt="Default Profile">
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Login</a>
                        <a href="register.php" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
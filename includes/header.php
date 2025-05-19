<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/header.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/form.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/dashboard.css">
</head>

<body>
    <header>
        <div class="container" id="header-container">
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="logo"><a href="<?php echo SITE_URL; ?>"><?php echo SITE_NAME; ?></a></li>
                    <div class="nav-right">
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?php echo SITE_URL; ?>/<?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/logout.php">Logout</a></li>
                            <li class="user-menu">
                                <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $_SESSION['profile_picture'] ?? 'default.png'; ?>" alt="Profile">
                                <!-- <span><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></span> -->
                            </li>
                        <?php else: ?>
                            <li><a href="<?php echo SITE_URL; ?>/login.php">Login</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/register.php">Register</a></li>
                        <?php endif; ?>
                    </div>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
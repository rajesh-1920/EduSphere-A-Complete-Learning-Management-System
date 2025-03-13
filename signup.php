<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Login & Signup</title>
    <!-- <link rel="stylesheet" href="styles.css"> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> -->
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">LMS</h1>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="courses.php">Courses</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="login.php" class="btn">Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Signup Form -->
    <section class="auth-container" id="signup">
        <div class="form-box">
            <h2>Signup</h2>
            <form action="signup.php" method="POST">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role">
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                </select>
                <button type="submit" class="btn">Signup</button>
                <p>Already have an account? <a href="login.php">Login</a></p>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 LMS. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Login & Signup</title>
    <link rel="stylesheet" href="css/signup.css">
</head>

<body>
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
</body>

</html>
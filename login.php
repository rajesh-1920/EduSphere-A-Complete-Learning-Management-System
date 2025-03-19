<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Login & Signup</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <!-- Login Form -->
    <section class="auth-container">
        <div class="form-box">
            <h2>Login</h2>
            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="btn">Login</button>
                <p>Don't have an account? <a href="signup.php">Sign up</a></p>
            </form>
        </div>
    </section>
</body>

</html>
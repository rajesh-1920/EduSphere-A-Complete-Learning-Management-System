<?php
include("function/login-signup.php");
$signup_obj = new login_signup();
if (isset($_POST["signup"])) {
    $is_insert = $signup_obj->insert_signup_data($_POST);
    header("Location: login.php?signup=$is_insert");
    exit();
}
?>

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
            <form action="" method="POST">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="number" name="std-id" placeholder="Student Id" required>
                <select name="role">
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" class="btn" name="signup">Signup</button>
                <p>Already have an account? <a href="login.php">Login</a></p>
            </form>
        </div>
    </section>
</body>

</html>
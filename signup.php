<?php
include("function/login-signup.php");
$obj_login_signup = new login_signup();
if (isset($_POST["add_signup"])) {
    $is_insert = $obj_login_signup->insert_signup_data($_POST);
    header("Location: login.php");
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
                <select name="role">
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                </select>
                <button type="submit" class="btn" name="add_signup">Signup</button>
                <p>Already have an account? <a href="login.php">Login</a></p>
            </form>
        </div>
    </section>
</body>

</html>
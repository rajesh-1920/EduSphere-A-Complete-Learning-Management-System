<?php
if (isset($_GET['signup']) && $_GET["signup"] == "success") {
    $success_message = "Signup successful! Please log in";
}

include("function/login-signup.php");
$login_obj = new login_signup();
if (isset($_POST["login"])) {
    $login_message = $login_obj->login_section($_POST);
    if ($login_message == "success") {
        header("Location: index.php?login=success");
        exit();
    }
}

?>
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
        <div>
            <?php
            if (isset($success_message)) {
                echo $success_message;
            } ?>
        </div>
        <div class="form-box">
            <h2>Login</h2>
            <form action="" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role">
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                </select>
                <div class="messege">
                    <?php
                    if (isset($login_message)) {
                        echo $login_message;
                    } ?>
                </div>
                <button type="submit" class="btn" name="login">Login</button>
                <p class="sign">Don't have an account? <a href="signup.php">Sign up</a></p>
            </form>
        </div>
    </section>
</body>

</html>
<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . (hasRole('admin') ? 'admin_dashboard.php' : (hasRole('instructor') ? 'instructor_dashboard.php' : 'student_dashboard.php')));
    exit();
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Redirect based on role
            $redirect = 'student_dashboard.php';
            if ($user['role'] === 'admin') {
                $redirect = 'admin_dashboard.php';
            } elseif ($user['role'] === 'instructor') {
                $redirect = 'instructor_dashboard.php';
            }
            
            $_SESSION['success_message'] = 'Login successful! Welcome back, ' . $user['full_name'] . '!';
            header("Location: $redirect");
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = "Login";
require_once 'header.php';
?>

<div style="max-width: 500px; margin: 50px auto; padding: 30px; background-color: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
    <h2 class="text-center" style="margin-bottom: 30px;">Login to Your Account</h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            <a href="forgot_password.php">Forgot password?</a>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Login</button>
        </div>
    </form>
    
    <div class="text-center" style="margin-top: 20px;">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>

<?php
require_once 'footer.php';
?>
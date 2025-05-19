<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$errors = [];
$username = $email = $full_name = $role = '';

// Define instructor registration code (move this to config.php in production)
define('INSTRUCTOR_CODE', 'INSTRUCTOR2023');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);
    $registration_code = isset($_POST['registration_code']) ? sanitize($_POST['registration_code']) : '';

    // Validate inputs
    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    } elseif (strlen($username) < 4) {
        $errors['username'] = 'Username must be at least 4 characters.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($full_name)) {
        $errors['full_name'] = 'Full name is required.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // Validate instructor registration
    if ($role === 'instructor') {
        if (empty($registration_code)) {
            $errors['registration_code'] = 'Registration code is required for instructor accounts.';
        } elseif ($registration_code !== INSTRUCTOR_CODE) {
            $errors['registration_code'] = 'Invalid instructor registration code.';
        }
    }

    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            if ($existingUser['username'] === $username) {
                $errors['username'] = 'Username already taken.';
            }
            if ($existingUser['email'] === $email) {
                $errors['email'] = 'Email already registered.';
            }
        }
    }

    // If no errors, create user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, full_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword, $role, $full_name]);

            $_SESSION['success_message'] = 'Registration successful! Please login to continue.';
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = "Register";
require_once 'header.php';
?>

<div style="max-width: 600px; margin: 50px auto; padding: 30px; background-color: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
    <h2 class="text-center" style="margin-bottom: 30px;">Create Your Account</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin-bottom: 0;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
            <?php if (isset($errors['username'])): ?>
                <small class="text-danger"><?php echo $errors['username']; ?></small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
            <?php if (isset($errors['email'])): ?>
                <small class="text-danger"><?php echo $errors['email']; ?></small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="full_name" class="form-label">Full Name</label>
            <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($full_name); ?>" required>
            <?php if (isset($errors['full_name'])): ?>
                <small class="text-danger"><?php echo $errors['full_name']; ?></small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="role" class="form-label">Account Type</label>
            <select id="role" name="role" class="form-control" required>
                <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
                <option value="instructor" <?= $role === 'instructor' ? 'selected' : '' ?>>Instructor</option>
            </select>
        </div>

        <div class="form-group" id="codeGroup" style="display: none;">
            <label for="registration_code" class="form-label">Instructor Registration Code</label>
            <input type="text" id="registration_code" name="registration_code" class="form-control">
            <?php if (isset($errors['registration_code'])): ?>
                <small class="text-danger"><?php echo $errors['registration_code']; ?></small>
            <?php endif; ?>
        </div>
        <!-- INSTRUCTOR2023 -->
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
            <?php if (isset($errors['password'])): ?>
                <small class="text-danger"><?php echo $errors['password']; ?></small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            <?php if (isset($errors['confirm_password'])): ?>
                <small class="text-danger"><?php echo $errors['confirm_password']; ?></small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Register</button>
        </div>
    </form>

    <div class="text-center" style="margin-top: 20px;">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<script>
    // Show registration code field only when instructor is selected
    document.getElementById('role').addEventListener('change', function() {
        const codeGroup = document.getElementById('codeGroup');
        codeGroup.style.display = this.value === 'instructor' ? 'block' : 'none';

        // Trigger change event on page load if instructor was selected
        if (this.value === 'instructor') {
            codeGroup.style.display = 'block';
        }
    });
</script>

<?php
require_once 'footer.php';
?>
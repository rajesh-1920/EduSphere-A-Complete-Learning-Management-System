<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['admin']);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $role = sanitize($_POST['role']);

    // Validate inputs
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($password)) $errors[] = 'Password is required';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (empty($role)) $errors[] = 'Role is required';

    // Check if username or email already exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = 'Username or email already exists';
    }

    // Handle profile picture upload
    $profilePicture = 'default.png';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['profile_picture'], PROFILE_PICTURE_PATH);
        if ($uploadResult['success']) {
            $profilePicture = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['message'];
        }
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, role, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $email, $passwordHash, $firstName, $lastName, $role, $profilePicture);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'User added successfully!';
            redirect('manage_users.php');
        } else {
            $errors[] = 'Failed to add user. Please try again.';
        }
    }
}

$pageTitle = 'Add New User';
require_once '../../includes/header.php';
?>

<div class="add-user-form">
    <h1>Add New User</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="instructor">Instructor</option>
                <option value="student">Student</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="form-group">
            <label for="profile_picture">Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture">
        </div>
        <button type="submit" class="btn">Add User</button>
        <a href="manage_users.php" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
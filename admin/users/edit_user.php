<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
checkRole(['admin']);

if (!isset($_GET['id'])) {
    redirect('manage_users.php');
}

$userId = $_GET['id'];
$user = getUserById($userId);
if (!$user) {
    redirect('manage_users.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $role = sanitize($_POST['role']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate inputs
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (empty($role)) $errors[] = 'Role is required';
    if (!empty($password) && $password !== $confirmPassword) $errors[] = 'Passwords do not match';

    // Check if username or email already exists (excluding current user)
    $stmt = $db->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
    $stmt->bind_param("ssi", $username, $email, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = 'Username or email already exists';
    }

    // Handle profile picture upload
    $profilePicture = $user['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['profile_picture'], PROFILE_PICTURE_PATH);
        if ($uploadResult['success']) {
            // Delete old profile picture if it's not the default
            if ($profilePicture !== 'default.png') {
                @unlink(PROFILE_PICTURE_PATH . $profilePicture);
            }
            $profilePicture = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['message'];
        }
    }

    if (empty($errors)) {
        // Prepare the update query
        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, profile_picture = ?, password_hash = ? WHERE user_id = ?");
            $stmt->bind_param("sssssssi", $username, $email, $firstName, $lastName, $role, $profilePicture, $passwordHash, $userId);
        } else {
            $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, profile_picture = ? WHERE user_id = ?");
            $stmt->bind_param("ssssssi", $username, $email, $firstName, $lastName, $role, $profilePicture, $userId);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'User updated successfully!';
            redirect('manage_users.php');
        } else {
            $errors[] = 'Failed to update user. Please try again.';
        }
    }
}

$pageTitle = 'Edit User';
require_once '../../includes/header.php';
?>

<div class="edit-user-form">
    <h1>Edit User: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
    
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
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="instructor" <?php echo $user['role'] === 'instructor' ? 'selected' : ''; ?>>Instructor</option>
                <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>
        <div class="form-group">
            <label for="profile_picture">Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture">
            <?php if ($user['profile_picture']): ?>
                <div class="current-image">
                    <p>Current Image:</p>
                    <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $user['profile_picture']; ?>" alt="Profile Picture" width="100">
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn">Update User</button>
        <a href="manage_users.php" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
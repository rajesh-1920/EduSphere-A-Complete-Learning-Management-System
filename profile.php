<?php
require_once 'config.php';
requireLogin();

$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
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
    
    // Check if username or email already exists (excluding current user)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $stmt->execute([$username, $email, $userId]);
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
    
    // Handle password change if provided
    $password_changed = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors['current_password'] = 'Current password is required to change password.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors['current_password'] = 'Current password is incorrect.';
        }
        
        if (empty($new_password)) {
            $errors['new_password'] = 'New password is required.';
        } elseif (strlen($new_password) < 6) {
            $errors['new_password'] = 'New password must be at least 6 characters.';
        }
        
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
        
        if (empty($errors)) {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $password_changed = true;
        }
    }
    
    // Handle profile picture upload
    $profile_picture = $user['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['profile_picture']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $uploadedFile = uploadFile($_FILES['profile_picture'], 'profile/');
            if ($uploadedFile) {
                // Delete old profile picture if it's not the default
                if ($profile_picture !== 'default.png' && file_exists(UPLOAD_DIR . 'profile/' . $profile_picture)) {
                    unlink(UPLOAD_DIR . 'profile/' . $profile_picture);
                }
                $profile_picture = basename($uploadedFile);
            } else {
                $errors['profile_picture'] = 'Failed to upload profile picture.';
            }
        } else {
            $errors['profile_picture'] = 'Only JPG, PNG, and GIF files are allowed.';
        }
    }
    
    // If no errors, update user
    if (empty($errors)) {
        try {
            if ($password_changed) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, password = ?, profile_picture = ? WHERE user_id = ?");
                $stmt->execute([$username, $email, $full_name, $hashedPassword, $profile_picture, $userId]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, profile_picture = ? WHERE user_id = ?");
                $stmt->execute([$username, $email, $full_name, $profile_picture, $userId]);
            }
            
            // Update session data
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['full_name'] = $full_name;
            
            $_SESSION['success_message'] = 'Profile updated successfully!';
            header("Location: profile.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}

$pageTitle = "Profile";
require_once 'header.php';
?>

<div class="dashboard">
    <div class="sidebar">
        <h3 class="sidebar-title">Profile</h3>
        <ul class="sidebar-menu">
            <li><a href="profile.php" class="active"><i class="fas fa-user"></i> My Profile</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h2>My Profile</h2>
        </div>
        
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul style="margin-bottom: 0;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="profile.php" enctype="multipart/form-data">
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                        <div>
                            <div class="form-group" style="text-align: center;">
                                <div style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; margin: 0 auto 20px; border: 3px solid var(--primary-color);">
                                    <img src="<?php echo !empty($user['profile_picture']) ? UPLOAD_DIR . 'profile/' . $user['profile_picture'] : 'assets/default.png'; ?>" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                
                                <input type="file" id="profile_picture" name="profile_picture" class="file-input" style="display: none;">
                                <label for="profile_picture" class="btn btn-outline" style="display: inline-block;">
                                    <i class="fas fa-camera"></i> Change Photo
                                </label>
                                <?php if (isset($errors['profile_picture'])): ?>
                                    <small class="text-danger"><?php echo $errors['profile_picture']; ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Joined Date</label>
                                <input type="text" class="form-control" value="<?php echo date('M j, Y', strtotime($user['created_at'])); ?>" readonly>
                            </div>
                        </div>
                        
                        <div>
                            <div class="form-group">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                <?php if (isset($errors['username'])): ?>
                                    <small class="text-danger"><?php echo $errors['username']; ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <small class="text-danger"><?php echo $errors['email']; ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                <?php if (isset($errors['full_name'])): ?>
                                    <small class="text-danger"><?php echo $errors['full_name']; ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="current_password" class="form-label">Current Password (to change password)</label>
                                <div style="position: relative;">
                                    <input type="password" id="current_password" name="current_password" class="form-control">
                                    <span class="password-toggle" style="position: absolute; right: 10px; top: 10px; cursor: pointer;">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <?php if (isset($errors['current_password'])): ?>
                                    <small class="text-danger"><?php echo $errors['current_password']; ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password" class="form-label">New Password</label>
                                <div style="position: relative;">
                                    <input type="password" id="new_password" name="new_password" class="form-control">
                                    <span class="password-toggle" style="position: absolute; right: 10px; top: 10px; cursor: pointer;">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <?php if (isset($errors['new_password'])): ?>
                                    <small class="text-danger"><?php echo $errors['new_password']; ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <div style="position: relative;">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                                    <span class="password-toggle" style="position: absolute; right: 10px; top: 10px; cursor: pointer;">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <small class="text-danger"><?php echo $errors['confirm_password']; ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Update Profile</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Password toggle functionality
document.querySelectorAll('.password-toggle').forEach(toggle => {
    toggle.addEventListener('click', function() {
        const input = this.previousElementSibling;
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});

// Profile picture preview
document.getElementById('profile_picture').addEventListener('change', function() {
    const preview = document.querySelector('.form-group img');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php
require_once 'footer.php';
?>
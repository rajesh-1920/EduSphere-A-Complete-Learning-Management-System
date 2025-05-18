<?php
require_once 'config.php';
requireLogin();

$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    }
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    if (empty($full_name)) {
        $errors['full_name'] = 'Full name is required';
    }

    // Check if changing password
    $password_changed = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors['current_password'] = 'Current password is required';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors['current_password'] = 'Current password is incorrect';
        }
        if (empty($new_password)) {
            $errors['new_password'] = 'New password is required';
        } elseif (strlen($new_password) < 6) {
            $errors['new_password'] = 'Password must be at least 6 characters';
        }
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_changed = true;
        }
    }

    // Handle profile picture upload
    $profile_picture = $user['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];

        if (in_array($file_type, $allowed_types)) {
            $uploaded = uploadFile($_FILES['profile_picture'], 'profile/');
            if ($uploaded) {
                // Delete old profile picture if not default
                if ($profile_picture !== 'default.png' && file_exists(UPLOAD_DIR . 'profile/' . $profile_picture)) {
                    unlink(UPLOAD_DIR . 'profile/' . $profile_picture);
                }
                $profile_picture = basename($uploaded);
            } else {
                $errors['profile_picture'] = 'Failed to upload profile picture';
            }
        } else {
            $errors['profile_picture'] = 'Only JPG, PNG, and GIF files are allowed';
        }
    }

    // Update user if no errors
    if (empty($errors)) {
        try {
            if ($password_changed) {
                $stmt = $pdo->prepare("UPDATE users SET 
                                      username = ?, email = ?, full_name = ?, 
                                      password = ?, profile_picture = ?
                                      WHERE user_id = ?");
                $stmt->execute([$username, $email, $full_name, $hashed_password, $profile_picture, $userId]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET 
                                      username = ?, email = ?, full_name = ?, 
                                      profile_picture = ?
                                      WHERE user_id = ?");
                $stmt->execute([$username, $email, $full_name, $profile_picture, $userId]);
            }

            // Update session data
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['full_name'] = $full_name;

            $_SESSION['success_message'] = 'Profile updated successfully!';
            header("Location: settings.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}

$pageTitle = "Settings";
require_once 'header.php';
?>

<div class="dashboard">
    <div class="sidebar">
        <h3 class="sidebar-title">My Learning</h3>
        <ul class="sidebar-menu">
            <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
            <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="grades.php"><i class="fas fa-chart-bar"></i> Grades</a></li>
            <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>Account Settings</h2>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post" action="settings.php" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group text-center">
                                <div class="profile-picture-container mb-3">
                                    <img src="<?= !empty($user['profile_picture']) ?
                                                    UPLOAD_DIR . 'profile/' . $user['profile_picture'] :
                                                    'assets/default.png' ?>"
                                        id="profile-picture-preview"
                                        class="rounded-circle"
                                        width="150" height="150">
                                </div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input"
                                        id="profile_picture" name="profile_picture">
                                    <label class="custom-file-label" for="profile_picture">
                                        Choose new photo
                                    </label>
                                </div>
                                <?php if (isset($errors['profile_picture'])): ?>
                                    <small class="text-danger"><?= $errors['profile_picture'] ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username"
                                    name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                                <?php if (isset($errors['username'])): ?>
                                    <small class="text-danger"><?= $errors['username'] ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email"
                                    name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <small class="text-danger"><?= $errors['email'] ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" class="form-control" id="full_name"
                                    name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                <?php if (isset($errors['full_name'])): ?>
                                    <small class="text-danger"><?= $errors['full_name'] ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h4 class="mb-4">Change Password</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" class="form-control"
                                    id="current_password" name="current_password">
                                <?php if (isset($errors['current_password'])): ?>
                                    <small class="text-danger"><?= $errors['current_password'] ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" class="form-control"
                                    id="new_password" name="new_password">
                                <?php if (isset($errors['new_password'])): ?>
                                    <small class="text-danger"><?= $errors['new_password'] ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" class="form-control"
                                    id="confirm_password" name="confirm_password">
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <small class="text-danger"><?= $errors['confirm_password'] ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h4 class="mb-4">Account Actions</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5>Export Data</h5>
                                <p>Download all your personal data in a portable format</p>
                                <button class="btn btn-outline-primary">Request Data Export</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5>Delete Account</h5>
                                <p>Permanently delete your account and all associated data</p>
                                <button class="btn btn-outline-danger" data-toggle="modal"
                                    data-target="#deleteAccountModal">
                                    Delete Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Account Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                <p>All your data, including course progress and submissions, will be permanently deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form method="post" action="delete_account.php" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Delete My Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Profile picture preview
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profile-picture-preview').src = e.target.result;
            }
            reader.readAsDataURL(file);
            document.querySelector('.custom-file-label').textContent = file.name;
        }
    });
</script>

<?php require_once 'footer.php'; ?>
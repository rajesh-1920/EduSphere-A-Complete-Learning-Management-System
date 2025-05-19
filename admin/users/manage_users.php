<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
checkRole(['admin']);

$db = new Database();
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Users';
require_once '../../includes/header.php';
?>

<div class="manage-users">
    <h1>Manage Users</h1>
    
    <div class="action-bar">
        <a href="add_user.php" class="btn">Add New User</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td><?php echo formatDate($user['created_at']); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn">Edit</a>
                        <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" class="btn delete-btn">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
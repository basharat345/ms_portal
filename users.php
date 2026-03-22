<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
require 'includes/header.php';
require 'config/db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $new_role = $_POST['role'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $new_role]);
        header("Location: users.php?success=1");
        exit;
    } catch(PDOException $e) {
        $error = "Error adding user: Email might already exist.";
    }
}

if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if($_GET['delete'] != $_SESSION['user_id']) { // prevent self-deletion
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$_GET['delete']]);
    }
    header("Location: users.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$role_labels = [
    'admin' => 'Administrator',
    'field_manager' => 'Field Manager',
    'cs_team' => 'CS Team',
    'production' => 'Production Team',
    'mis' => 'MIS Team',
    'mystery_shopper' => 'Mystery Shopper'
];
?>
<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <div class="header-title">
                <div style="display:flex; align-items:center; gap: 15px;">
                    <button class="mobile-nav-toggle"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1>User Management</h1>
                        <p>Add staff and mystery shoppers.</p>
                    </div>
                </div>
            </div>
            <?php include 'includes/profile_dropdown.php'; ?>
        </header>

        <?php if(isset($_GET['success'])): ?>
            <div style="background: #27AE60; color: white; padding: 12px; border-radius: 10px; margin-bottom: 24px; text-align: center; font-weight: 500;">
                <i class="fas fa-check-circle" style="margin-right:8px;"></i> User added successfully!
            </div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div style="background: #E74C3C; color: white; padding: 12px; border-radius: 10px; margin-bottom: 24px; text-align: center; font-weight: 500;">
                <i class="fas fa-exclamation-circle" style="margin-right:8px;"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="grid" style="grid-template-columns: 1fr 2fr; gap: 20px;">
            <div class="glass-panel">
                <h3 style="margin-bottom: 20px;">Add New User</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="mystery_shopper">Mystery Shopper</option>
                            <option value="cs_team">Client Services (CS) Team</option>
                            <option value="production">Production Team</option>
                            <option value="field_manager">Field Manager</option>
                            <option value="mis">MIS Team</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-primary" style="width: 100%; margin-top:10px;"><i class="fas fa-plus"></i> Add User</button>
                </form>
            </div>

            <div class="glass-panel" style="overflow-x: auto;">
                <h3 style="margin-bottom: 20px;">System Users</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="badge" style="background: var(--bg-hover); color: var(--text-main); font-weight:500;"><?= htmlspecialchars($role_labels[$u['role']] ?? $u['role']) ?></span></td>
                            <td><?= substr($u['created_at'], 0, 10) ?></td>
                            <td>
                                <?php if($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?delete=<?= $u['id'] ?>" class="btn btn-secondary" style="padding: 4px 10px; color: #E74C3C; border-color: #fceceb; background: transparent;" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>

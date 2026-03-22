<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require 'includes/header.php';
require 'config/db.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?")->execute([$name, $email, $_SESSION['user_id']]);
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $success = "Profile updated successfully!";
        $user['name'] = $name;
        $user['email'] = $email;
    }
    elseif(isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        if(!password_verify($current, $user['password'])) {
            $error = "Current password is incorrect!";
        } elseif($new !== $confirm) {
            $error = "New passwords do not match!";
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $_SESSION['user_id']]);
            $success = "Password changed successfully!";
            $user['password'] = $hash;
        }
    }
}
$action = $_GET['action'] ?? 'profile';
?>
<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <div class="header-title">
                <div style="display:flex; align-items:center; gap: 15px;">
                    <button class="mobile-nav-toggle"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1>Account Settings</h1>
                        <p>Manage your profile and security.</p>
                    </div>
                </div>
            </div>
            <?php include 'includes/profile_dropdown.php'; ?>
        </header>

        <div class="glass-panel" style="max-width: 600px; margin: 0 auto;">
            
            <div style="display:flex; border-bottom: 1px solid rgba(0,0,0,0.1); margin-bottom: 25px;">
                <a href="profile.php" style="padding: 10px 20px; text-decoration: none; color: <?= $action=='profile'?'var(--primary)':'var(--text-muted)' ?>; border-bottom: 2px solid <?= $action=='profile'?'var(--primary)':'transparent' ?>; font-weight: 600;"><i class="fas fa-user"></i> Profile Info</a>
                <a href="profile.php?action=password" style="padding: 10px 20px; text-decoration: none; color: <?= $action=='password'?'var(--primary)':'var(--text-muted)' ?>; border-bottom: 2px solid <?= $action=='password'?'var(--primary)':'transparent' ?>; font-weight: 600;"><i class="fas fa-lock"></i> Security</a>
            </div>

            <?php if(isset($success)): ?>
                <div style="background: #27AE60; color: white; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><i class="fas fa-check-circle"></i> <?= $success ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div style="background: #E74C3C; color: white; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>

            <?php if($action == 'profile'): ?>
                <form method="POST" action="profile.php">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars(ucwords(str_replace('_', ' ', $user['role']))) ?>" disabled style="opacity: 0.7; background: #f8f9fa;">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </form>
            <?php else: ?>
                <form method="POST" action="profile.php?action=password">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary" style="background: #E67E22; border-color: #E67E22;"><i class="fas fa-key"></i> Update Password</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>

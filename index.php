<?php
require 'includes/header.php';
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
require 'config/db.php';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<div class="auth-wrapper">
    <div class="glass-panel auth-card">
        <div class="auth-header">
            <div class="logo-icon"><i class="fas fa-chart-pie"></i></div>
            <h2>Welcome to MRS Pro</h2>
            <p>Sign in to your account</p>
        </div>
        
        <?php if($error): ?>
            <div style="background: var(--accent); color: white; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="admin@mrspro.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="admin123">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; font-size:1rem; padding: 12px;">Sign In</button>
        </form>
        <p style="text-align: center; margin-top: 24px; font-size: 0.85rem; color: var(--text-muted); line-height:1.6;">
            Demo credentials:<br>admin@mrspro.com / admin123<br>shopper@mrspro.com / admin123
        </p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

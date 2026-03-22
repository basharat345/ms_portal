<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require 'includes/header.php';
require 'config/db.php';
if(!in_array($_SESSION['role'], ['admin', 'field_manager'])) {
    header("Location: dashboard.php");
    exit;
}

if(isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $pdo->exec("UPDATE branches SET status = 'in_edit' WHERE id = " . (int)$_GET['approve']);
    header("Location: field_manager_review.php?success=1");
    exit;
}

if(isset($_POST['reject_id']) && isset($_POST['reason'])) {
    $branch_id = (int)$_POST['reject_id'];
    $reason = $_POST['reason'];
    $pdo->exec("DELETE FROM submissions WHERE branch_id = $branch_id");
    $stmt = $pdo->prepare("UPDATE branches SET status = 'assigned', disapproval_reason = ? WHERE id = ?");
    $stmt->execute([$reason, $branch_id]);
    header("Location: field_manager_review.php?rejected=1");
    exit;
}

$stmt = $pdo->query("SELECT b.*, p.name as project_name FROM branches b JOIN projects p ON b.project_id = p.id WHERE b.status = 'submitted' ORDER BY b.id DESC");
$branches = $stmt->fetchAll();
?>
<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <div class="header-title">
                <div style="display:flex; align-items:center; gap: 15px;">
                    <button class="mobile-nav-toggle"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1>Field Manager Review</h1>
                        <p>Review shopper submissions before sending to production.</p>
                    </div>
                </div>
            </div>
            <?php include 'includes/profile_dropdown.php'; ?>
        </header>

        <?php if(isset($_GET['success'])): ?>
            <div style="background: #27AE60; color: white; padding: 12px; border-radius: 10px; margin-bottom: 24px; text-align: center; font-weight: 500;">
                <i class="fas fa-check-circle" style="margin-right:8px;"></i> Submission approved and sent to Production Queue!
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['rejected'])): ?>
            <div style="background: #E74C3C; color: white; padding: 12px; border-radius: 10px; margin-bottom: 24px; text-align: center; font-weight: 500;">
                <i class="fas fa-times-circle" style="margin-right:8px;"></i> Submission rejected and returned to Shopper!
            </div>
        <?php endif; ?>

        <div class="glass-panel">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($branches as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['project_name']) ?></td>
                        <td><?= htmlspecialchars($b['branch_name']) ?></td>
                        <td><span class="badge badge-pending">Submitted (Pending FM Review)</span></td>
                        <td>
                            <a href="#" onclick="rejectSubmission(<?= $b['id'] ?>); return false;" class="btn btn-secondary" style="padding: 5px 10px; font-size:0.8rem; background:#E74C3C; border-color:#E74C3C; color:white; margin-right: 5px;"><i class="fas fa-times"></i> Disapprove</a>
                            <a href="field_manager_review.php?approve=<?= $b['id'] ?>" class="btn btn-primary" style="padding: 5px 10px; font-size:0.8rem;"><i class="fas fa-check"></i> Approve</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($branches) == 0): ?>
                    <tr><td colspan="4" style="text-align:center; padding: 20px;">No pending reviews.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<script>
function rejectSubmission(id) {
    let reason = prompt('Please enter the reason for disapproval explicitly stating what needs fixing:');
    if(reason !== null && reason.trim() !== '') {
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = 'field_manager_review.php';
        
        let inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'reject_id';
        inputId.value = id;
        form.appendChild(inputId);
        
        let inputReason = document.createElement('input');
        inputReason.type = 'hidden';
        inputReason.name = 'reason';
        inputReason.value = reason;
        form.appendChild(inputReason);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php include 'includes/footer.php'; ?>

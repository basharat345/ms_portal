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

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_branch'])) {
    $branch_id = $_POST['branch_id'];
    $shopper_id = $_POST['shopper_id'];
    $pdo->prepare("UPDATE branches SET assigned_shopper_id = ?, status = 'assigned' WHERE id = ?")->execute([$shopper_id, $branch_id]);
    
    $proj = $pdo->query("SELECT project_id FROM branches WHERE id = " . (int)$branch_id)->fetchColumn();
    $pdo->prepare("UPDATE projects SET status = 'execution' WHERE id = ?")->execute([$proj]);

    header("Location: dispatch.php?success=1");
    exit;
}

$stmt = $pdo->query("SELECT b.*, p.name as project_name FROM branches b JOIN projects p ON b.project_id = p.id WHERE b.status = 'pending' ORDER BY p.created_at DESC");
$branches = $stmt->fetchAll();

$branches_grouped = [];
foreach($branches as $b) {
    if(!isset($branches_grouped[$b['project_name']])) {
        $branches_grouped[$b['project_name']] = [];
    }
    $branches_grouped[$b['project_name']][] = $b;
}

$shoppersStmt = $pdo->query("SELECT id, name FROM users WHERE role = 'mystery_shopper'");
$shoppers = $shoppersStmt->fetchAll();
?>
<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <div class="header-title">
                <div style="display:flex; align-items:center; gap: 15px;">
                    <button class="mobile-nav-toggle"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1>Field Dispatch</h1>
                        <p>Assign shoppers to pending branches.</p>
                    </div>
                </div>
            </div>
            <?php include 'includes/profile_dropdown.php'; ?>
        </header>

        <?php if(isset($_GET['success'])): ?>
            <div style="background: #27AE60; color: white; padding: 12px; border-radius: 10px; margin-bottom: 24px; text-align: center; font-weight: 500;">
                <i class="fas fa-check-circle" style="margin-right:8px;"></i> Shopper successfully assigned!
            </div>
        <?php endif; ?>

        <?php if(count($branches) == 0): ?>
            <div class="glass-panel" style="text-align:center; padding: 40px; color: var(--text-muted);">
                <i class="fas fa-check-circle" style="font-size:3rem; margin-bottom:15px; color: var(--primary-light); opacity: 0.5;"></i><br>No pending branches to dispatch. Great job!
            </div>
        <?php else: ?>
            <?php foreach($branches_grouped as $project_name => $project_branches): ?>
            <div class="glass-panel" style="margin-bottom: 30px;">
                <h3 style="margin-bottom: 15px; border-bottom: 2px solid rgba(0,0,0,0.05); padding-bottom: 10px;">
                    <i class="fas fa-folder-open" style="color:var(--primary); margin-right:8px;"></i> Project: &nbsp;<?= htmlspecialchars($project_name) ?>
                </h3>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th>City</th>
                                <th>Assign Shopper</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($project_branches as $b): ?>
                            <tr>
                                <td style="min-width:200px;"><strong><?= htmlspecialchars($b['branch_name']) ?></strong> <br><small style="color:var(--text-muted);">(<?= htmlspecialchars($b['branch_code']) ?>)</small></td>
                                <td><?= htmlspecialchars($b['city']) ?></td>
                                <td>
                                    <form method="POST" action="" style="display:flex; gap:10px; flex-wrap:wrap;">
                                        <input type="hidden" name="branch_id" value="<?= $b['id'] ?>">
                                        <select name="shopper_id" class="form-control" style="width: auto; padding: 6px 10px; flex-grow:1;" required>
                                            <option value="">Select Shopper...</option>
                                            <?php foreach($shoppers as $s): ?>
                                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="assign_branch" class="btn btn-primary" style="padding: 6px 16px; font-size:0.8rem; white-space:nowrap;">Assign</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>
<?php include 'includes/footer.php'; ?>

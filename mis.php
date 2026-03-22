<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require 'includes/header.php';
require 'config/db.php';
if(!in_array($_SESSION['role'], ['admin', 'mis'])) {
    header("Location: dashboard.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mis_complete'])) {
    $branch_id = $_POST['branch_id'];
    $report_path = '';
    if(isset($_FILES['final_report']) && $_FILES['final_report']['error'] == 0) {
        $upload_dir = 'uploads/mis/';
        if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $report_path = $upload_dir . time() . '_' . basename($_FILES['final_report']['name']);
        move_uploaded_file($_FILES['final_report']['tmp_name'], $report_path);
    }
    
    $pdo->prepare("UPDATE branches SET status = 'done', mis_report_path = ? WHERE id = ?")->execute([$report_path, $branch_id]);
    
    $proj = $pdo->query("SELECT project_id FROM branches WHERE id = " . (int)$branch_id)->fetchColumn();
    $pending_count = $pdo->query("SELECT COUNT(*) FROM branches WHERE project_id = $proj AND status != 'done'")->fetchColumn();
    
    if($pending_count == 0) {
        $pdo->prepare("UPDATE projects SET status = 'delivered' WHERE id = ?")->execute([$proj]);
    }
    
    header("Location: mis.php?success=1");
    exit;
}

$stmt = $pdo->query("SELECT b.*, p.name as project_name FROM branches b JOIN projects p ON b.project_id = p.id WHERE b.status = 'mis_compile' ORDER BY b.id DESC");
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
                        <h1>MIS Compilation</h1>
                        <p>Upload final reports and deliver to clients.</p>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/profile_dropdown.php'; ?>
        </header>

        <?php if(isset($_GET['success'])): ?>
            <div style="background: #27AE60; color: white; padding: 12px; border-radius: 10px; margin-bottom: 24px; text-align: center; font-weight: 500;">
                <i class="fas fa-check-circle" style="margin-right:8px;"></i> Final report successfully uploaded and marked as delivered!
            </div>
        <?php endif; ?>

        <div class="glass-panel">
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Upload Final Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($branches) > 0): ?>
                            <?php foreach($branches as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['project_name']) ?></td>
                                <td><?= htmlspecialchars($b['branch_name']) ?></td>
                                <td><span class="badge badge-pending">Compiling</span></td>
                                <td>
                                    <form method="POST" action="" enctype="multipart/form-data" style="display:flex; gap:10px; align-items:center;">
                                        <input type="hidden" name="branch_id" value="<?= $b['id'] ?>">
                                        <input type="file" name="final_report" required style="font-size:0.8rem; width:220px; border:1px solid #ccc; padding:4px; border-radius:6px;">
                                        <button type="submit" name="mis_complete" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;"><i class="fas fa-file-export"></i> Compile & Finish</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; padding: 40px; color: var(--text-muted);"><i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 15px; color: var(--primary-light); opacity:0.5;"></i><br>MIS queue is empty. Great job!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>

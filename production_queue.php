<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require 'includes/header.php';
require 'config/db.php';
if(!in_array($_SESSION['role'], ['admin', 'production'])) {
    header("Location: dashboard.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['production_complete'])) {
    $branch_id = $_POST['branch_id'];
    $video_path = '';
    if(isset($_FILES['edited_video']) && $_FILES['edited_video']['error'] == 0) {
        $upload_dir = 'uploads/production/';
        if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $video_path = $upload_dir . time() . '_' . basename($_FILES['edited_video']['name']);
        move_uploaded_file($_FILES['edited_video']['tmp_name'], $video_path);
    }
    
    $pdo->prepare("UPDATE branches SET status = 'mis_compile', production_video_path = ? WHERE id = ?")->execute([$video_path, $branch_id]);
    header("Location: production_queue.php?success=1");
    exit;
}

$stmt = $pdo->query("SELECT b.*, p.name as project_name FROM branches b JOIN projects p ON b.project_id = p.id WHERE b.status = 'in_edit' ORDER BY b.id DESC");
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
                        <h1>Production Queue</h1>
                        <p>Video processing, subtitling, and report compilation.</p>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/profile_dropdown.php'; ?>
        </header>
        
        <?php if(isset($_GET['success'])): ?>
            <div style="background: #27AE60; color: white; padding: 12px; border-radius: 10px; margin-bottom: 24px; text-align: center; font-weight: 500;">
                <i class="fas fa-check-circle" style="margin-right:8px;"></i> File uploaded and sent to MIS compilation successfully!
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
                            <th>Upload Edited Video</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($branches) > 0): ?>
                            <?php foreach($branches as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['project_name']) ?></td>
                                <td><?= htmlspecialchars($b['branch_name']) ?></td>
                                <td><span class="badge badge-active">In Production</span></td>
                                <td>
                                    <form method="POST" action="" enctype="multipart/form-data" style="display:flex; gap:10px; align-items:center;">
                                        <input type="hidden" name="branch_id" value="<?= $b['id'] ?>">
                                        <input type="file" name="edited_video" required accept="video/*" style="font-size:0.8rem; width:220px; border:1px solid #ccc; padding:4px; border-radius:6px;">
                                        <button type="submit" name="production_complete" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;"><i class="fas fa-upload"></i> Send to MIS</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; padding: 40px; color: var(--text-muted);"><i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 15px; color: var(--primary-light); opacity:0.5;"></i><br>Production queue is empty. Great job!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>

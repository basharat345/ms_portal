<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require 'includes/header.php';
require 'config/db.php';
if(!in_array($_SESSION['role'], ['admin', 'cs_team'])) {
    header("Location: dashboard.php");
    exit;
}

$project_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if(!$project) die("Invalid project.");

$stmt = $pdo->prepare("
    SELECT b.*, s.video_path as shopper_video, s.form_data 
    FROM branches b 
    LEFT JOIN submissions s ON b.id = s.branch_id 
    WHERE b.project_id = ? 
    ORDER BY b.id ASC
");
$stmt->execute([$project_id]);
$branches = $stmt->fetchAll();
?>
<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <div class="header-title">
                <div style="display:flex; align-items:center; gap: 15px;">
                    <a href="projects.php" class="btn btn-secondary" style="padding: 8px 14px;"><i class="fas fa-arrow-left"></i></a>
                    <div>
                        <h1>Project Hub: <?= htmlspecialchars($project['name']) ?></h1>
                        <p>Status: <span class="badge badge-<?= $project['status'] == 'delivered' ? 'done' : 'active' ?>"><?= htmlspecialchars(ucfirst($project['status'])) ?></span></p>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/profile_dropdown.php'; ?>
        </header>

        <div class="glass-panel" style="margin-bottom: 20px;">
            <h3 style="margin-bottom: 15px;">Initial Project Files</h3>
            <div style="display:flex; gap: 15px; flex-wrap: wrap;">
                <?php if($project['script_file']): ?>
                    <a href="<?= htmlspecialchars($project['script_file']) ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-word" style="color:#2980B9;"></i> Download Script</a>
                <?php endif; ?>
                <?php if($project['audio_file']): ?>
                    <a href="<?= htmlspecialchars($project['audio_file']) ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-audio" style="color:#E67E22;"></i> Download Audio Instruction</a>
                <?php else: ?>
                    <span style="color:var(--text-muted); font-size:0.9rem;">No audio instructions provided.</span>
                <?php endif; ?>
                <?php if(!$project['script_file'] && !$project['audio_file']): ?>
                    <span style="color:var(--text-muted); font-size:0.9rem;">No initial files uploaded.</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="glass-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Branch Tracking & File Repository</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Shopper File (RAW)</th>
                            <th>Production (EDITED)</th>
                            <th>MIS (FINAL REPORT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($branches as $b): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($b['branch_name']) ?></strong><br><small style="color:var(--text-muted);"><?= htmlspecialchars($b['city']) ?></small></td>
                            <td><span class="badge badge-<?= $b['status'] == 'done' ? 'done' : 'pending' ?>"><?= htmlspecialchars(ucfirst($b['status'])) ?></span></td>
                            
                            <td>
                                <?php if($b['shopper_video']): ?>
                                    <a href="<?= htmlspecialchars($b['shopper_video']) ?>" target="_blank" style="color: var(--primary); font-weight:500; text-decoration:none;"><i class="fas fa-video"></i> Raw Video</a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php if($b['production_video_path']): ?>
                                    <a href="<?= htmlspecialchars($b['production_video_path']) ?>" target="_blank" style="color: #9B59B6; font-weight:500; text-decoration:none;"><i class="fas fa-film"></i> Edited File</a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php if($b['mis_report_path']): ?>
                                    <a href="<?= htmlspecialchars($b['mis_report_path']) ?>" target="_blank" style="color: #27AE60; font-weight:500; text-decoration:none;"><i class="fas fa-file-pdf"></i> Final Report</a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($branches) == 0): ?>
                            <tr><td colspan="5" style="text-align:center; padding: 30px;">No branches associated with this project.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>

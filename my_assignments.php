<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mystery_shopper') {
    header("Location: index.php");
    exit;
}
require 'includes/header.php';
require 'config/db.php';

$stmt = $pdo->prepare("SELECT b.*, p.name as project_name, p.start_date, p.deadline FROM branches b JOIN projects p ON b.project_id = p.id WHERE b.assigned_shopper_id = ? ORDER BY b.id DESC");
$stmt->execute([$_SESSION['user_id']]);
$assignments = $stmt->fetchAll();

$active_grouped = [];
$completed_list = [];

foreach($assignments as $b) {
    if($b['status'] == 'assigned' || $b['status'] == 'pending') {
        if(!isset($active_grouped[$b['project_name']])) {
            $active_grouped[$b['project_name']] = [];
        }
        $active_grouped[$b['project_name']][] = $b;
    } else {
        $completed_list[] = $b;
    }
}
?>
<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <div class="header-title">
                <div style="display:flex; align-items:center; gap: 15px;">
                    <button class="mobile-nav-toggle"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1>My Assignments</h1>
                        <p>View and complete your assigned store visits.</p>
                    </div>
                </div>
            </div>
            <?php include 'includes/profile_dropdown.php'; ?>
        </header>

        <?php if(empty($active_grouped) && empty($completed_list)): ?>
            <div class="glass-panel" style="text-align:center; padding: 40px; color:var(--text-muted);">
                <i class="fas fa-check-circle" style="font-size:3rem; opacity:0.3; margin-bottom:10px;"></i><br>No assignments yet.
            </div>
        <?php endif; ?>

        <?php if(!empty($active_grouped)): ?>
            <?php foreach($active_grouped as $project_name => $project_branches): ?>
            <div class="glass-panel" style="margin-bottom: 30px;">
                <h3 style="margin-bottom: 15px; border-bottom: 2px solid rgba(0,0,0,0.05); padding-bottom: 10px;">
                    <i class="fas fa-folder" style="color:var(--primary); margin-right:8px;"></i> Project: &nbsp;<?= htmlspecialchars($project_name) ?>
                </h3>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th>Timeline</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($project_branches as $b): ?>
                            <tr>
                                <td style="min-width:200px;">
                                    <strong><?= htmlspecialchars($b['branch_name']) ?></strong> <br><small style="color:var(--text-muted);">(<?= htmlspecialchars($b['branch_code']) ?>)</small>
                                    <?php if(!empty($b['disapproval_reason'])): ?>
                                        <div style="margin-top: 8px; font-size: 0.8rem; background: #FEF2F2; color: #DC2626; padding: 5px 8px; border-radius: 4px; border: 1px solid #FCA5A5;">
                                            <strong><i class="fas fa-exclamation-circle"></i> Disapproved:</strong> <?= nl2br(htmlspecialchars($b['disapproval_reason'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="min-width:140px;">
                                    <div style="font-size:0.85rem; line-height: 1.6;">
                                        <div style="color:var(--text-main);"><i class="fas fa-calendar-alt" style="color:var(--primary); margin-right:5px; width:14px; text-align:center;"></i> <?= htmlspecialchars(date('d M, Y', strtotime($b['start_date']))) ?></div>
                                        <div style="color:var(--text-muted);"><i class="fas fa-flag-checkered" style="color:#E67E22; margin-right:5px; width:14px; text-align:center;"></i> <?= htmlspecialchars(date('d M, Y', strtotime($b['deadline']))) ?></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($b['city']) ?></td>
                                <td><span class="badge badge-<?= $b['status'] == 'pending' ? 'pending' : 'active' ?>"><?= htmlspecialchars(ucfirst($b['status'])) ?></span></td>
                                <td>
                                    <?php if($b['status'] == 'assigned' || $b['status'] == 'pending'): ?>
                                        <a href="shopper_portal.php?branch_id=<?= $b['id'] ?>" class="btn btn-primary" style="padding: 6px 12px; font-size:0.8rem;">Start Report</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if(count($completed_list) > 0): ?>
            <div class="glass-panel" style="margin-bottom: 30px; border-left: 4px solid #27AE60;">
                <h3 style="margin-bottom: 15px; border-bottom: 2px solid rgba(0,0,0,0.05); padding-bottom: 10px; color: #27AE60;">
                    <i class="fas fa-check-circle" style="margin-right:8px;"></i> Completed Work
                </h3>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Branch</th>
                                <th>Timeline</th>
                                <th>City</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($completed_list as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['project_name']) ?></td>
                                <td><strong><?= htmlspecialchars($b['branch_name']) ?></strong> <br><small style="color:var(--text-muted);">(<?= htmlspecialchars($b['branch_code']) ?>)</small></td>
                                <td style="min-width:140px;">
                                    <div style="font-size:0.85rem; line-height: 1.6;">
                                        <div style="color:var(--text-main);"><i class="fas fa-calendar-alt" style="color:var(--primary); margin-right:5px; width:14px; text-align:center;"></i> <?= htmlspecialchars(date('d M, Y', strtotime($b['start_date']))) ?></div>
                                        <div style="color:var(--text-muted);"><i class="fas fa-flag-checkered" style="color:#E67E22; margin-right:5px; width:14px; text-align:center;"></i> <?= htmlspecialchars(date('d M, Y', strtotime($b['deadline']))) ?></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($b['city']) ?></td>
                                <td><span class="badge badge-done"><?= htmlspecialchars(ucfirst($b['status'])) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>
<?php include 'includes/footer.php'; ?>

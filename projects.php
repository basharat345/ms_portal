<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require 'includes/header.php';
require 'config/db.php';

$stmt = $pdo->query("SELECT p.*, u.name as creator_name FROM projects p LEFT JOIN users u ON p.created_by = u.id ORDER BY p.created_at DESC");
$projects = $stmt->fetchAll();
?>
<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <div class="header-title">
                <div style="display:flex; align-items:center; gap: 15px;">
                    <button class="mobile-nav-toggle"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1>Projects Directory</h1>
                        <p>Manage mystery shopping and research projects.</p>
                    </div>
                </div>
            </div>
            <?php include 'includes/profile_dropdown.php'; ?>
        </header>

        <div style="margin-bottom: 20px;">
            <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'cs_team' || $_SESSION['role'] == 'field_manager'): ?>
            <a href="project_create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> &nbsp; New Project
            </a>
            <?php endif; ?>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
            <div style="background: #27AE60; color: white; padding: 12px; border-radius: 10px; margin-bottom: 24px; text-align: center; font-weight: 500;">
                <i class="fas fa-check-circle" style="margin-right:8px;"></i> Project successfully created!
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div style="background: #E74C3C; color: white; padding: 16px; border-radius: 10px; margin-bottom: 24px; font-weight: 500; line-height:1.5;">
                <i class="fas fa-exclamation-triangle" style="margin-right:8px; font-size:1.2rem;"></i> <strong>Data Import Warning</strong><br><br>
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="glass-panel">
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Creator</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($projects) > 0): ?>
                            <?php foreach($projects as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['project_code']) ?></td>
                                <td><?= htmlspecialchars($p['name']) ?></td>
                                <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $p['type']))) ?></td>
                                <td><span class="badge badge-<?= $p['status'] == 'delivered' ? 'done' : ($p['status'] == 'execution' ? 'active' : 'pending') ?>"><?= htmlspecialchars(ucfirst($p['status'])) ?></span></td>
                                <td><?= htmlspecialchars($p['creator_name']) ?></td>
                                <td>
                                    <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'cs_team'): ?>
                                        <a href="project_details.php?id=<?= $p['id'] ?>" class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;"><i class="fas fa-eye"></i> View Hub</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center; padding: 40px; color: var(--text-muted);"><i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 10px; opacity: 0.2;"></i><br>No projects found. Create one.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>

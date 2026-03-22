<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
if($_SESSION['role'] === 'mystery_shopper') {
    header("Location: my_assignments.php");
    exit;
}
require 'includes/header.php';
require 'config/db.php';
$role_names = [
    'admin' => 'System Administrator',
    'cs_team' => 'Client Services',
    'field_manager' => 'Field Manager',
    'production' => 'Production Team',
    'mis' => 'MIS Team'
];

$active_projects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status != 'delivered'")->fetchColumn();
$pending_branches = $pdo->query("SELECT COUNT(*) FROM branches WHERE status = 'pending'")->fetchColumn();
$videos_processed = $pdo->query("SELECT COUNT(*) FROM branches WHERE status IN ('done', 'mis_compile')")->fetchColumn();
$recent_projects = $pdo->query("SELECT p.project_code, p.name, p.status, p.deadline, p.expected_branches, 
    (SELECT COUNT(*) FROM branches b WHERE b.project_id = p.id) as total_branches,
    (SELECT COUNT(*) FROM branches b WHERE b.project_id = p.id AND b.status IN ('submitted', 'in_edit', 'mis_compile', 'done')) as completed_branches 
    FROM projects p ORDER BY p.created_at DESC LIMIT 5")->fetchAll();
?>
<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <header class="top-header">
            <div class="header-title">
                <div style="display:flex; align-items:center; gap: 15px;">
                    <button class="mobile-nav-toggle"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1>Dashboard</h1>
                        <p>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</p>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/profile_dropdown.php'; ?>
        </header>

        <div class="grid">
            <a href="projects.php" style="text-decoration:none; color:inherit; display:block;">
                <div class="glass-panel stat-card" style="cursor:pointer; transition: all 0.2s; border: 1px solid transparent;" onmouseover="this.style.transform='translateY(-5px)'; this.style.borderColor='var(--primary)';" onmouseout="this.style.transform=''; this.style.borderColor='transparent';">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-folder"></i></div>
                    </div>
                    <div class="stat-info">
                        <h3><?= $active_projects ?></h3>
                        <p>Active Projects</p>
                    </div>
                </div>
            </a>
            
            <a href="dispatch.php" style="text-decoration:none; color:inherit; display:block;">
                <div class="glass-panel stat-card" style="cursor:pointer; transition: all 0.2s; border: 1px solid transparent;" onmouseover="this.style.transform='translateY(-5px)'; this.style.borderColor='#D97706';" onmouseout="this.style.transform=''; this.style.borderColor='transparent';">
                    <div class="stat-header">
                        <div class="stat-icon" style="background:#FEF3C7; color:#D97706;"><i class="fas fa-clock"></i></div>
                    </div>
                    <div class="stat-info">
                        <h3><?= $pending_branches ?></h3>
                        <p>Pending Branches</p>
                    </div>
                </div>
            </a>
            
            <a href="projects.php" style="text-decoration:none; color:inherit; display:block;">
                <div class="glass-panel stat-card" style="cursor:pointer; transition: all 0.2s; border: 1px solid transparent;" onmouseover="this.style.transform='translateY(-5px)'; this.style.borderColor='#059669';" onmouseout="this.style.transform=''; this.style.borderColor='transparent';">
                    <div class="stat-header">
                        <div class="stat-icon" style="background:#D1FAE5; color:#059669;"><i class="fas fa-video"></i></div>
                    </div>
                    <div class="stat-info">
                        <h3><?= $videos_processed ?></h3>
                        <p>Videos Processed</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="glass-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Recent Activity</h3>
                <a href="projects.php" class="btn btn-secondary">View All</a>
            </div>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project Code</th>
                            <th>Project Name</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recent_projects) > 0): ?>
                            <?php foreach($recent_projects as $p): 
                                $total = max((int)$p['total_branches'], (int)$p['expected_branches']);
                                $progress_text = $p['completed_branches'] . '/' . ($total > 0 ? $total : 0);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($p['project_code']) ?></td>
                                <td><?= htmlspecialchars($p['name']) ?></td>
                                <td><span class="badge badge-pending" style="background:#E0F2FE; color:#0284C7; border: 1px solid #BAE6FD;"><?= $progress_text ?></span></td>
                                <td><span class="badge badge-<?= $p['status'] == 'delivered' ? 'done' : ($p['status'] == 'execution' ? 'active' : 'pending') ?>"><?= htmlspecialchars(ucfirst($p['status'])) ?></span></td>
                                <td><?= htmlspecialchars($p['deadline']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; padding: 20px; color: var(--text-muted);">No recent projects found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>

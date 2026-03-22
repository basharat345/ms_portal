<?php 
$role = $_SESSION['role'] ?? ''; 
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon"><i class="fas fa-chart-pie"></i></div>
        <h2>MRS Pro</h2>
    </div>
    
    <?php if($role != 'mystery_shopper'): ?>
        <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        
        <?php if($role == 'admin'): ?>
        <a href="users.php" class="nav-link <?= $current_page == 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> User Management
        </a>
        <?php endif; ?>
        
        <?php if($role == 'admin' || $role == 'cs_team' || $role == 'field_manager'): ?>
        <a href="projects.php" class="nav-link <?= $current_page == 'projects.php' ? 'active' : '' ?>">
            <i class="fas fa-folder-open"></i> Projects
        </a>
        <?php endif; ?>
        
        <?php if($role == 'admin' || $role == 'field_manager'): ?>
        <a href="dispatch.php" class="nav-link <?= $current_page == 'dispatch.php' ? 'active' : '' ?>">
            <i class="fas fa-paper-plane"></i> Dispatch
        </a>
        <?php endif; ?>
        
        <?php if($role == 'admin' || $role == 'production'): ?>
        <a href="production_queue.php" class="nav-link <?= $current_page == 'production_queue.php' ? 'active' : '' ?>">
            <i class="fas fa-video"></i> Production
        </a>
        <?php endif; ?>
        
        <?php if($role == 'admin' || $role == 'field_manager'): ?>
        <a href="field_manager_review.php" class="nav-link <?= $current_page == 'field_manager_review.php' ? 'active' : '' ?>">
            <i class="fas fa-check-double"></i> Field Manager Review
        </a>
        <?php endif; ?>
        
        <?php if($role == 'admin' || $role == 'mis'): ?>
        <a href="mis.php" class="nav-link <?= $current_page == 'mis.php' ? 'active' : '' ?>">
            <i class="fas fa-database"></i> MIS Compilation
        </a>
        <?php endif; ?>
    <?php else: ?>
        <a href="my_assignments.php" class="nav-link <?= $current_page == 'my_assignments.php' ? 'active' : '' ?>">
            <i class="fas fa-tasks"></i> My Assignments
        </a>
    <?php endif; ?>
    
    <a href="logout.php" class="nav-link" style="margin-top: auto; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 20px;">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</aside>

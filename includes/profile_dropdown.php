<?php
// Resolve role name
$role_names = [
    'admin' => 'System Admin',
    'cs_team' => 'Client Services',
    'field_manager' => 'Field Manager',
    'production' => 'Production Team',
    'mis' => 'MIS Team',
    'mystery_shopper' => 'Mystery Shopper'
];
$display_role = $role_names[$_SESSION['role']] ?? 'User';
?>
<div class="user-profile" onclick="toggleProfileDropdown(event)" style="cursor: pointer; position: relative; display: flex; align-items: center; gap: 15px;">
    <div class="user-info" style="text-align: right;">
        <h4 style="margin: 0; font-size: 0.95rem; color: var(--text-main);"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></h4>
        <span style="font-size: 0.8rem; color: var(--text-muted);"><?= $display_role ?></span>
    </div>
    <div class="avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; cursor: pointer;">
        <?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>
    </div>
    
    <!-- Dropdown Menu -->
    <div id="profileDropdown" style="display:none; position:absolute; top:50px; right:0; background:white; width:220px; box-shadow:0 10px 30px rgba(0,0,0,0.1); border-radius:12px; border:1px solid rgba(0,0,0,0.05); z-index:9999; overflow:hidden;">
        <div style="padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); background: #F8FAFC; text-align: left;">
            <strong style="display:block; color:var(--text-main); font-size: 0.95rem;"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></strong>
            <small style="color:var(--text-muted); font-size: 0.8rem;"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></small>
        </div>
        <div style="text-align: left;">
            <a href="profile.php" style="display:block; padding:12px 15px; text-decoration:none; color:var(--text-main); border-bottom: 1px solid rgba(0,0,0,0.05); transition:background 0.2s;" onmouseover="this.style.background='#f1f5f9';" onmouseout="this.style.background='white';">
                <i class="fas fa-user-edit" style="width:20px; color:var(--primary); text-align: center; margin-right: 5px;"></i> Update Profile
            </a>
            <a href="profile.php?action=password" style="display:block; padding:12px 15px; text-decoration:none; color:var(--text-main); border-bottom: 1px solid rgba(0,0,0,0.05); transition:background 0.2s;" onmouseover="this.style.background='#f1f5f9';" onmouseout="this.style.background='white';">
                <i class="fas fa-lock" style="width:20px; color:#E67E22; text-align: center; margin-right: 5px;"></i> Change Password
            </a>
            <a href="logout.php" style="display:block; padding:12px 15px; text-decoration:none; color:#E74C3C; transition:background 0.2s; background:#FEF2F2;" onmouseover="this.style.background='#fee2e2';" onmouseout="this.style.background='#FEF2F2';">
                <i class="fas fa-sign-out-alt" style="width:20px; text-align: center; margin-right: 5px;"></i> Logout
            </a>
        </div>
    </div>
</div>

<script>
function toggleProfileDropdown(e) {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    e.stopPropagation();
}
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('profileDropdown');
    if(dropdown && dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    }
});
</script>

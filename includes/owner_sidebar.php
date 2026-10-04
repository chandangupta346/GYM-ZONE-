<?php
// includes/owner_sidebar.php - Gym Owner Navigation Sidebar
$currentScript = basename($_SERVER['PHP_SELF']);
$user = currentUser();
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-profile">
        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>">
        <div class="sidebar-profile-info">
            <div class="sidebar-profile-name"><?= htmlspecialchars($user['name']) ?></div>
            <span class="sidebar-role-badge badge-owner">GYM OWNER</span>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="sidebar-nav-item <?= ($currentScript === 'dashboard.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/owner/dashboard.php">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="sidebar-nav-item <?= ($currentScript === 'my-gyms.php' || $currentScript === 'edit-gym.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/owner/my-gyms.php">
                <i class="fas fa-building"></i> <span>My Gyms</span>
            </a>
        </li>
        <li class="sidebar-nav-item <?= ($currentScript === 'add-gym.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/owner/add-gym.php">
                <i class="fas fa-plus-circle"></i> <span>Add New Gym</span>
            </a>
        </li>
        <li class="sidebar-nav-item <?= ($currentScript === 'bookings.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/owner/bookings.php">
                <i class="fas fa-ticket-alt"></i> <span>Trial Bookings</span>
            </a>
        </li>
        <li class="sidebar-nav-item <?= ($currentScript === 'plans.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/owner/plans.php">
                <i class="fas fa-tags"></i> <span>Membership Plans</span>
            </a>
        </li>
    </ul>

    <div style="border-top:1px solid var(--border-color); padding-top:16px; margin-top:auto;">
        <a href="<?= BASE_URL ?>/index.php" class="btn-gz btn-gz-outline btn-gz-sm" style="width:100%; margin-bottom:8px;">
            <i class="fas fa-arrow-left"></i> Public Website
        </a>
        <a href="<?= BASE_URL ?>/auth/logout.php" style="color:var(--danger); font-size:13px; display:flex; align-items:center; gap:8px; padding:8px 12px;">
            <i class="fas fa-sign-out-alt"></i> Sign Out
        </a>
    </div>
</aside>

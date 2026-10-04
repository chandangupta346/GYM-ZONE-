<?php
// includes/admin_sidebar.php - Admin Console Sidebar Navigation
$currentScript = basename($_SERVER['PHP_SELF']);
$user = currentUser();
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-profile">
        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>">
        <div class="sidebar-profile-info">
            <div class="sidebar-profile-name"><?= htmlspecialchars($user['name']) ?></div>
            <span class="sidebar-role-badge badge-admin">PLATFORM ADMIN</span>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="sidebar-nav-item <?= ($currentScript === 'dashboard.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/dashboard.php">
                <i class="fas fa-shield-alt"></i> <span>Overview</span>
            </a>
        </li>
        <li class="sidebar-nav-item <?= ($currentScript === 'gyms.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/gyms.php">
                <i class="fas fa-building"></i> <span>Manage Gyms</span>
            </a>
        </li>
        <li class="sidebar-nav-item <?= ($currentScript === 'users.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/users.php">
                <i class="fas fa-users"></i> <span>Users &amp; Owners</span>
            </a>
        </li>
        <li class="sidebar-nav-item <?= ($currentScript === 'bookings.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/bookings.php">
                <i class="fas fa-ticket-alt"></i> <span>All Bookings</span>
            </a>
        </li>
        <li class="sidebar-nav-item <?= ($currentScript === 'reviews.php') ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/reviews.php">
                <i class="fas fa-star"></i> <span>Reviews</span>
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

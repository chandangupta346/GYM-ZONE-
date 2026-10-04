<?php
// includes/navbar.php - Global Navigation Bar
$currentPage = basename($_SERVER['PHP_SELF']);
$user = currentUser();
?>
<nav class="gym-navbar">
    <div class="gym-container">
        <div class="nav-wrapper">
            <!-- Brand Logo -->
            <a href="<?= BASE_URL ?>/index.php" class="gym-brand">
                <div class="brand-icon">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <div class="brand-text">
                    <span class="title">GYM<span>ZONE</span></span>
                    <span class="subtitle">Explore Your Fitness</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <ul class="nav-menu" id="navMenu">
                <li>
                    <a href="<?= BASE_URL ?>/index.php" class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>">Home</a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/explore.php" class="nav-link <?= ($currentPage === 'explore.php') ? 'active' : '' ?>">Explore Gyms</a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/new-openings.php" class="nav-link <?= ($currentPage === 'new-openings.php') ? 'active' : '' ?>">
                        New Openings <span class="badge-new-nav">NEW</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/book-trial.php" class="nav-link <?= ($currentPage === 'book-trial.php') ? 'active' : '' ?>">Book Trial</a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/about.php" class="nav-link <?= ($currentPage === 'about.php') ? 'active' : '' ?>">About</a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/contact.php" class="nav-link <?= ($currentPage === 'contact.php') ? 'active' : '' ?>">Contact</a>
                </li>
            </ul>

            <!-- Nav Actions (Auth / User Profile / CTA) -->
            <div class="nav-actions">
                <?php if ($user): ?>
                    <div class="user-dropdown">
                        <button type="button" class="user-menu-btn" id="userMenuBtn">
                            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>">
                            <span style="font-weight:600; font-size:13px;"><?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></span>
                            <i class="fas fa-chevron-down" style="font-size:11px; color:var(--text-muted);"></i>
                        </button>
                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <div style="padding: 10px 18px; border-bottom: 1px solid var(--border-color);">
                                <strong style="display:block; font-size:14px; color:#fff;"><?= htmlspecialchars($user['name']) ?></strong>
                                <small style="color:var(--text-muted); font-size:11px; text-transform:uppercase; font-weight:700; color:var(--primary);"><?= strtoupper($user['role']) ?> ACCOUNT</small>
                            </div>
                            
                            <?php if ($user['role'] === 'admin'): ?>
                                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="user-dropdown-item">
                                    <i class="fas fa-shield-alt text-warning"></i> Admin Console
                                </a>
                            <?php elseif ($user['role'] === 'owner'): ?>
                                <a href="<?= BASE_URL ?>/owner/dashboard.php" class="user-dropdown-item">
                                    <i class="fas fa-store-alt text-primary"></i> Owner Dashboard
                                </a>
                            <?php endif; ?>

                            <a href="<?= BASE_URL ?>/user/profile.php" class="user-dropdown-item">
                                <i class="fas fa-user-circle"></i> My Profile
                            </a>
                            <a href="<?= BASE_URL ?>/user/favorites.php" class="user-dropdown-item">
                                <i class="fas fa-heart text-danger"></i> Saved Gyms
                            </a>
                            <a href="<?= BASE_URL ?>/user/bookings.php" class="user-dropdown-item">
                                <i class="fas fa-calendar-check text-success"></i> My Bookings
                            </a>

                            <div class="user-dropdown-divider"></div>

                            <a href="<?= BASE_URL ?>/auth/logout.php" class="user-dropdown-item" style="color:var(--danger);">
                                <i class="fas fa-sign-out-alt"></i> Sign Out
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/login.php" class="btn-gz btn-gz-outline btn-gz-sm">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?= BASE_URL ?>/auth/register.php?role=owner" class="btn-gz btn-gz-secondary btn-gz-sm d-none-mobile">
                        <i class="fas fa-building"></i> List Your Gym
                    </a>
                <?php endif; ?>

                <button class="mobile-toggle" id="mobileMenuToggle" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

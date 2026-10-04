<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? sanitize($page_title) . ' | GYMZONE' : 'GYMZONE - Discover & Book Gyms Near You'; ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Teko:wght@600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Header / Navbar -->
    <header class="navbar">
        <div class="container">
            <a href="index.php" class="brand-logo">
                <i class="fas fa-dumbbell"></i> GYM<span>ZONE</span>
            </a>

            <button class="nav-toggle" id="navToggle" aria-label="Toggle Navigation">
                <i class="fas fa-bars"></i>
            </button>

            <nav class="nav-menu" id="navMenu">
                <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
                <a href="explore.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'explore.php' && !isset($_GET['is_new'])) ? 'active' : ''; ?>">Explore Gyms</a>
                <a href="explore.php?is_new=1" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'explore.php' && isset($_GET['is_new'])) ? 'active' : ''; ?>">New Gyms</a>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (getUserRole() === 'user'): ?>
                        <a href="user-dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'user-dashboard.php' ? 'active' : ''; ?>">My Dashboard</a>
                    <?php elseif (getUserRole() === 'owner'): ?>
                        <a href="owner-dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'owner-dashboard.php' ? 'active' : ''; ?>">Owner Dashboard</a>
                    <?php elseif (getUserRole() === 'admin'): ?>
                        <a href="admin-dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : ''; ?>">Admin Panel</a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>

            <div class="nav-auth">
                <?php if (isLoggedIn()): ?>
                    <div class="user-badge">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo sanitize($_SESSION['user_name'] ?? 'Account'); ?></span>
                    </div>
                    <a href="logout.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary btn-sm"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="register.php" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Global Flash Notification Container -->
    <div class="container" style="margin-top: 20px;">
        <?php echo displayFlash(); ?>
    </div>

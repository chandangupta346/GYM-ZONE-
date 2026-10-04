<?php
$page_title = "User Dashboard";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$user_id = getUserId();

try {
    // Fetch User Profile Info
    $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $user = $stmtUser->fetch();

    // Fetch User Bookings
    $stmtBookings = $pdo->prepare("
        SELECT b.*, g.name AS gym_name, g.area, g.address 
        FROM bookings b
        JOIN gyms g ON b.gym_id = g.id
        WHERE b.user_id = ? OR b.phone = ? OR b.email = ?
        ORDER BY b.created_at DESC
    ");
    $stmtBookings->execute([$user_id, $user['phone'], $user['email']]);
    $userBookings = $stmtBookings->fetchAll();

    // Fetch Saved Favorites
    $stmtFavs = $pdo->prepare("
        SELECT g.*, gi.image_url 
        FROM favorites f
        JOIN gyms g ON f.gym_id = g.id
        LEFT JOIN gym_images gi ON g.id = gi.gym_id AND gi.is_primary = 1
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmtFavs->execute([$user_id]);
    $favoriteGyms = $stmtFavs->fetchAll();

} catch (PDOException $e) {
    setFlash('error', 'Dashboard error: ' . $e->getMessage());
    $userBookings = [];
    $favoriteGyms = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="dashboard-layout">
        <!-- Sidebar Navigation -->
        <aside class="dashboard-sidebar">
            <div style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--accent-orange-glow); border: 2px solid var(--accent-orange); color: var(--accent-orange); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 10px;">
                    <i class="fas fa-user"></i>
                </div>
                <h3 style="font-size: 1.4rem;"><?php echo sanitize($user['full_name']); ?></h3>
                <p style="font-size: 0.82rem; color: var(--text-muted);"><?php echo sanitize($user['email']); ?></p>
                <span class="badge badge-orange" style="margin-top: 8px; inline-block;">FITNESS MEMBER</span>
            </div>

            <nav class="dash-menu">
                <a href="#bookings-section" class="dash-link active"><i class="fas fa-calendar-check text-orange"></i> My Bookings</a>
                <a href="#favorites-section" class="dash-link"><i class="fas fa-heart text-orange"></i> Saved Gyms</a>
                <a href="explore.php" class="dash-link"><i class="fas fa-compass text-orange"></i> Find More Gyms</a>
                <a href="logout.php" class="dash-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Main Dashboard Content -->
        <main>
            <div class="section-header" style="margin-bottom: 30px; border: none;">
                <div>
                    <h2 class="section-title">MY <span class="text-orange">DASHBOARD</span></h2>
                    <p class="subheading">Track your free trial visits and saved gym listings</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div>
                        <div class="stat-num"><?php echo count($userBookings); ?></div>
                        <div class="stat-title">Trial Bookings</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-ticket"></i></div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-num"><?php echo count($favoriteGyms); ?></div>
                        <div class="stat-title">Saved Favorites</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-heart"></i></div>
                </div>
            </div>

            <!-- Bookings History Table -->
            <div style="margin-bottom: 50px;" id="bookings-section">
                <h3 class="heading-sm" style="margin-bottom: 15px;"><i class="fas fa-history text-orange"></i> My Free Trial Bookings</h3>
                <?php if (!empty($userBookings)): ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Booking Code</th>
                                    <th>Gym Name</th>
                                    <th>Visit Date & Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userBookings as $b): ?>
                                    <tr>
                                        <td><strong class="text-orange"><?php echo sanitize($b['booking_code']); ?></strong></td>
                                        <td>
                                            <strong><?php echo sanitize($b['gym_name']); ?></strong><br>
                                            <span style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-map-marker-alt"></i> <?php echo sanitize($b['area']); ?></span>
                                        </td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($b['visit_date'])); ?><br>
                                            <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo sanitize($b['visit_time']); ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo sanitize($b['status']); ?>">
                                                <?php echo sanitize($b['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="gym-details.php?id=<?php echo $b['gym_id']; ?>" class="btn btn-secondary btn-sm">View Gym</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="background: var(--bg-card); padding: 30px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: center; color: var(--text-muted);">
                        <p>You haven't booked any gym trials yet.</p>
                        <a href="explore.php" class="btn btn-primary btn-sm" style="margin-top: 10px;">Explore Gyms Now</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Favorite Gyms Grid -->
            <div id="favorites-section">
                <h3 class="heading-sm" style="margin-bottom: 15px;"><i class="fas fa-heart text-orange"></i> My Saved Favorite Gyms</h3>
                <?php if (!empty($favoriteGyms)): ?>
                    <div class="gym-grid">
                        <?php foreach ($favoriteGyms as $gym): ?>
                            <?php $gymImg = getGymImageFallback($gym['image_url']); ?>
                            <div class="gym-card">
                                <div class="gym-card-img-wrap">
                                    <img src="<?php echo sanitize($gymImg); ?>" alt="<?php echo sanitize($gym['name']); ?>" class="gym-card-img">
                                    <button class="fav-btn active" data-gym-id="<?php echo $gym['id']; ?>" title="Remove Favorite">
                                        <i class="fas fa-heart text-orange"></i>
                                    </button>
                                </div>
                                <div class="gym-card-body">
                                    <h3 class="gym-title"><?php echo sanitize($gym['name']); ?></h3>
                                    <p class="gym-address"><i class="fas fa-map-marker-alt text-orange"></i> <?php echo sanitize($gym['area']); ?></p>
                                    <div class="gym-card-footer">
                                        <div class="gym-price"><?php echo formatCurrency($gym['price_per_month']); ?> <span>/ mo</span></div>
                                        <a href="gym-details.php?id=<?php echo $gym['id']; ?>" class="btn btn-primary btn-sm">Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="background: var(--bg-card); padding: 30px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: center; color: var(--text-muted);">
                        <p>No saved favorite gyms found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

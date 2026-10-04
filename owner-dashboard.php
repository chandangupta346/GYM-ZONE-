<?php
$page_title = "Gym Owner Dashboard";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('owner');
$owner_id = getUserId();

try {
    // Fetch Gyms owned by this owner
    $stmtGyms = $pdo->prepare("
        SELECT g.*, 
        (SELECT COUNT(*) FROM bookings WHERE gym_id = g.id) AS total_bookings,
        (SELECT image_url FROM gym_images WHERE gym_id = g.id AND is_primary = 1 LIMIT 1) AS image_url
        FROM gyms g
        WHERE g.owner_id = ?
        ORDER BY g.id DESC
    ");
    $stmtGyms->execute([$owner_id]);
    $ownedGyms = $stmtGyms->fetchAll();

    // Collect all Gym IDs for bookings query
    $gymIds = array_column($ownedGyms, 'id');

    if (!empty($gymIds)) {
        $inClause = implode(',', array_fill(0, count($gymIds), '?'));
        $stmtBookings = $pdo->prepare("
            SELECT b.*, g.name AS gym_name 
            FROM bookings b
            JOIN gyms g ON b.gym_id = g.id
            WHERE b.gym_id IN ($inClause)
            ORDER BY b.created_at DESC
        ");
        $stmtBookings->execute($gymIds);
        $ownerBookings = $stmtBookings->fetchAll();
    } else {
        $ownerBookings = [];
    }

} catch (PDOException $e) {
    setFlash('error', 'Owner dashboard error: ' . $e->getMessage());
    $ownedGyms = [];
    $ownerBookings = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="dashboard-layout">
        <!-- Owner Sidebar -->
        <aside class="dashboard-sidebar">
            <div style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(255, 87, 34, 0.15); border: 2px solid var(--accent-orange); color: var(--accent-orange); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 10px;">
                    <i class="fas fa-store"></i>
                </div>
                <h3 style="font-size: 1.4rem;"><?php echo sanitize($_SESSION['user_name']); ?></h3>
                <p style="font-size: 0.82rem; color: var(--text-muted);"><?php echo sanitize($_SESSION['user_email']); ?></p>
                <span class="badge badge-orange" style="margin-top: 8px; inline-block;">GYM OWNER</span>
            </div>

            <nav class="dash-menu">
                <a href="owner-dashboard.php" class="dash-link active"><i class="fas fa-chart-line text-orange"></i> Overview</a>
                <a href="add-gym.php" class="dash-link"><i class="fas fa-plus-circle text-orange"></i> Add New Gym</a>
                <a href="logout.php" class="dash-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main>
            <div class="section-header" style="margin-bottom: 30px; border: none; align-items: center;">
                <div>
                    <h2 class="section-title">OWNER <span class="text-orange">DASHBOARD</span></h2>
                    <p class="subheading">Manage your gym listings, photos, pricing plans, and trial visitor bookings</p>
                </div>
                <a href="add-gym.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Gym</a>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div>
                        <div class="stat-num"><?php echo count($ownedGyms); ?></div>
                        <div class="stat-title">Listed Gyms</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-dumbbell"></i></div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-num"><?php echo count($ownerBookings); ?></div>
                        <div class="stat-title">Trial Bookings</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-ticket"></i></div>
                </div>
            </div>

            <!-- Owned Gyms List -->
            <div style="margin-bottom: 50px;">
                <h3 class="heading-sm" style="margin-bottom: 15px;"><i class="fas fa-building text-orange"></i> My Listed Gyms</h3>
                <?php if (!empty($ownedGyms)): ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Gym Name</th>
                                    <th>Location / Area</th>
                                    <th>Monthly Fee</th>
                                    <th>Status</th>
                                    <th>Total Bookings</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ownedGyms as $g): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo sanitize($g['name']); ?></strong>
                                            <?php if ($g['is_new']): ?>
                                                <span class="badge badge-orange" style="font-size: 0.65rem;">NEW</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><i class="fas fa-map-marker-alt text-orange"></i> <?php echo sanitize($g['area']); ?></td>
                                        <td><strong><?php echo formatCurrency($g['price_per_month']); ?></strong></td>
                                        <td>
                                            <span class="status-badge status-<?php echo sanitize($g['status']); ?>">
                                                <?php echo sanitize($g['status']); ?>
                                            </span>
                                        </td>
                                        <td><strong><?php echo $g['total_bookings']; ?></strong> visits</td>
                                        <td>
                                            <div style="display: flex; gap: 6px;">
                                                <a href="gym-details.php?id=<?php echo $g['id']; ?>" class="btn btn-secondary btn-sm" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="edit-gym.php?id=<?php echo $g['id']; ?>" class="btn btn-outline btn-sm" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                                <a href="delete-gym.php?id=<?php echo $g['id']; ?>" class="btn btn-secondary btn-sm" style="color: var(--danger); border-color: var(--danger);" onclick="return confirm('Are you sure you want to delete this gym listing?');" title="Delete"><i class="fas fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="background: var(--bg-card); padding: 40px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: center;">
                        <p style="color: var(--text-muted); margin-bottom: 15px;">You have not listed any gyms yet.</p>
                        <a href="add-gym.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Your First Gym</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Manage Bookings Section -->
            <div>
                <h3 class="heading-sm" style="margin-bottom: 15px;"><i class="fas fa-clipboard-list text-orange"></i> Free Trial Visitor Bookings</h3>
                <?php if (!empty($ownerBookings)): ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Booking Code</th>
                                    <th>Gym</th>
                                    <th>Visitor Name</th>
                                    <th>Phone</th>
                                    <th>Visit Date & Time</th>
                                    <th>Status</th>
                                    <th>Change Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ownerBookings as $b): ?>
                                    <tr>
                                        <td><strong class="text-orange"><?php echo sanitize($b['booking_code']); ?></strong></td>
                                        <td><?php echo sanitize($b['gym_name']); ?></td>
                                        <td><strong><?php echo sanitize($b['full_name']); ?></strong></td>
                                        <td><?php echo sanitize($b['phone']); ?></td>
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
                                            <form action="update-booking-status.php" method="POST" style="display: flex; gap: 5px;">
                                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                <button type="submit" name="status" value="confirmed" class="btn btn-primary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;"><i class="fas fa-check"></i> Confirm</button>
                                                <button type="submit" name="status" value="cancelled" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: var(--danger);" onclick="return confirm('Cancel this booking?');"><i class="fas fa-times"></i> Cancel</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="background: var(--bg-card); padding: 30px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: center; color: var(--text-muted);">
                        <p>No bookings received for your gyms yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

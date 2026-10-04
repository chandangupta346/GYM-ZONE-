<?php
// owner/dashboard.php - Gym Owner Overview
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireRole(['owner', 'admin']);
$db = getDB();
$user = currentUser();

// Handle quick booking status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $bookingId = (int)$_POST['booking_id'];
    $newStatus = $_POST['new_status'];
    if (in_array($newStatus, ['pending', 'confirmed', 'attended', 'cancelled'])) {
        try {
            $upStmt = $db->prepare("UPDATE trial_bookings b 
                                    JOIN gyms g ON b.gym_id = g.id 
                                    SET b.status = ? 
                                    WHERE b.id = ? AND (g.owner_id = ? OR ? = 'admin')");
            $upStmt->execute([$newStatus, $bookingId, $user['id'], $user['role']]);
            setFlash('success', "Booking status updated to " . strtoupper($newStatus));
        } catch (Exception $e) {
            setFlash('error', 'Update error: ' . $e->getMessage());
        }
    }
}

// Fetch Metrics for this owner
try {
    // 1. Gyms count
    $gymsStmt = $db->prepare("SELECT COUNT(*) FROM gyms WHERE owner_id = ?");
    $gymsStmt->execute([$user['id']]);
    $totalGyms = $gymsStmt->fetchColumn();

    // 2. Bookings count & pending count
    $bookStmt = $db->prepare("SELECT COUNT(*) as total_bookings, 
                              SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                              SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings
                              FROM trial_bookings b 
                              JOIN gyms g ON b.gym_id = g.id 
                              WHERE g.owner_id = ?");
    $bookStmt->execute([$user['id']]);
    $bookStats = $bookStmt->fetch();

    // 3. Reviews count & avg rating
    $revStmt = $db->prepare("SELECT COUNT(*) as total_reviews, AVG(r.rating) as avg_rating 
                             FROM reviews r 
                             JOIN gyms g ON r.gym_id = g.id 
                             WHERE g.owner_id = ?");
    $revStmt->execute([$user['id']]);
    $revStats = $revStmt->fetch();

    // 4. Recent Bookings
    $recentStmt = $db->prepare("SELECT b.*, g.name as gym_name, g.area 
                                FROM trial_bookings b 
                                JOIN gyms g ON b.gym_id = g.id 
                                WHERE g.owner_id = ? 
                                ORDER BY b.created_at DESC LIMIT 5");
    $recentStmt->execute([$user['id']]);
    $recentBookings = $recentStmt->fetchAll();

    // 5. Gyms list preview
    $myGymsStmt = $db->prepare("SELECT * FROM gyms WHERE owner_id = ? ORDER BY id DESC LIMIT 4");
    $myGymsStmt->execute([$user['id']]);
    $myGyms = $myGymsStmt->fetchAll();
} catch (Exception $e) {
    die("Owner dashboard error: " . $e->getMessage());
}

$pageTitle = "Gym Owner Dashboard | GYMZONE";
$extraCss = "dashboard.css";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="dashboard-layout">
    <?php include __DIR__ . '/../includes/owner_sidebar.php'; ?>

    <main class="dashboard-content">
        <?= renderFlashMessages() ?>

        <div class="dash-header">
            <div class="dash-header-title">
                <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
                <p>Track your gym visits, trial requests, and member inquiries.</p>
            </div>
            <a href="<?= BASE_URL ?>/owner/add-gym.php" class="btn-gz btn-gz-primary">
                <i class="fas fa-plus"></i> Add New Gym
            </a>
        </div>

        <!-- Metrics Cards Row -->
        <div class="metrics-row">
            <div class="metric-card">
                <div class="metric-card-info">
                    <h4>My Fitness Centers</h4>
                    <div class="metric-num"><?= (int)$totalGyms ?></div>
                </div>
                <div class="metric-icon icon-orange">
                    <i class="fas fa-building"></i>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-card-info">
                    <h4>Total Trial Bookings</h4>
                    <div class="metric-num"><?= (int)($bookStats['total_bookings'] ?? 0) ?></div>
                </div>
                <div class="metric-icon icon-blue">
                    <i class="fas fa-ticket-alt"></i>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-card-info">
                    <h4>Pending Visits</h4>
                    <div class="metric-num"><?= (int)($bookStats['pending_bookings'] ?? 0) ?></div>
                </div>
                <div class="metric-icon icon-green">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-card-info">
                    <h4>Average Rating</h4>
                    <div class="metric-num"><?= number_format($revStats['avg_rating'] ?? 5.0, 1) ?> ★</div>
                </div>
                <div class="metric-icon icon-purple">
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>

        <!-- Recent Trial Requests Table -->
        <div class="dash-card">
            <div class="dash-card-header">
                <h3><i class="fas fa-calendar-alt text-primary"></i> Recent Free Trial Bookings</h3>
                <a href="<?= BASE_URL ?>/owner/bookings.php" class="btn-gz btn-gz-outline btn-gz-sm">View All Bookings</a>
            </div>

            <?php if (!empty($recentBookings)): ?>
                <div class="table-responsive">
                    <table class="gz-table">
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Visitor Details</th>
                                <th>Fitness Center</th>
                                <th>Visit Date &amp; Slot</th>
                                <th>Status</th>
                                <th>Update Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $b): ?>
                                <tr>
                                    <td>
                                        <strong style="color:var(--primary); font-family:monospace;"><?= htmlspecialchars($b['booking_ref']) ?></strong>
                                    </td>
                                    <td>
                                        <strong style="color:#fff; display:block;"><?= htmlspecialchars($b['user_name']) ?></strong>
                                        <small style="color:var(--text-secondary);"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($b['user_phone']) ?></small>
                                    </td>
                                    <td>
                                        <span style="color:#fff;"><?= htmlspecialchars($b['gym_name']) ?></span>
                                    </td>
                                    <td>
                                        <div style="color:#fff; font-weight:600;"><?= date('d M Y', strtotime($b['visit_date'])) ?></div>
                                        <small style="color:var(--text-muted);"><?= htmlspecialchars($b['time_slot']) ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        $st = $b['status'];
                                        $pillClass = ($st === 'confirmed') ? 'status-confirmed' : (($st === 'attended') ? 'status-attended' : (($st === 'cancelled') ? 'status-cancelled' : 'status-pending'));
                                        ?>
                                        <span class="status-pill <?= $pillClass ?>">
                                            <?= strtoupper($st) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:inline-flex; gap:6px;">
                                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                            <?php if ($b['status'] !== 'confirmed'): ?>
                                                <button type="submit" name="update_status" value="1" onclick="this.form.new_status.value='confirmed'" class="btn-gz btn-gz-secondary btn-gz-sm" title="Confirm Booking" style="color:var(--success);">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($b['status'] !== 'attended'): ?>
                                                <button type="submit" name="update_status" value="1" onclick="this.form.new_status.value='attended'" class="btn-gz btn-gz-secondary btn-gz-sm" title="Mark Attended" style="color:#60a5fa;">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($b['status'] !== 'cancelled'): ?>
                                                <button type="submit" name="update_status" value="1" onclick="this.form.new_status.value='cancelled'" class="btn-gz btn-gz-secondary btn-gz-sm" title="Cancel" style="color:var(--danger);">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <input type="hidden" name="new_status" value="">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color:var(--text-muted); font-size:14px; text-align:center; padding:30px;">
                    No trial session bookings received yet. When visitors request a free pass for your gym, they will appear here.
                </p>
            <?php endif; ?>
        </div>

        <!-- My Gyms Quick List -->
        <div class="dash-card">
            <div class="dash-card-header">
                <h3><i class="fas fa-store-alt text-primary"></i> My Gym Listings</h3>
                <a href="<?= BASE_URL ?>/owner/my-gyms.php" class="btn-gz btn-gz-outline btn-gz-sm">Manage All</a>
            </div>

            <?php if (!empty($myGyms)): ?>
                <div class="table-responsive">
                    <table class="gz-table">
                        <thead>
                            <tr>
                                <th>Gym Name</th>
                                <th>Area</th>
                                <th>Starting Price</th>
                                <th>Approval Status</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myGyms as $g): ?>
                                <tr>
                                    <td>
                                        <strong style="color:#fff;"><?= htmlspecialchars($g['name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($g['area']) ?></td>
                                    <td><strong style="color:var(--primary);"><?= formatPrice($g['starting_price']) ?></strong>/mo</td>
                                    <td>
                                        <span class="status-pill <?= ($g['status'] === 'approved') ? 'status-approved' : (($g['status'] === 'pending') ? 'status-pending' : 'status-rejected') ?>">
                                            <?= strtoupper($g['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= renderStars($g['rating'], true) ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="<?= BASE_URL ?>/gym-details.php?id=<?= $g['id'] ?>" class="btn-icon" title="View Public Page"><i class="fas fa-eye"></i></a>
                                            <a href="<?= BASE_URL ?>/owner/edit-gym.php?id=<?= $g['id'] ?>" class="btn-icon" title="Edit Listing"><i class="fas fa-edit"></i></a>
                                            <a href="<?= BASE_URL ?>/owner/plans.php?gym_id=<?= $g['id'] ?>" class="btn-icon" title="Manage Plans"><i class="fas fa-tags"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align:center; padding:30px;">
                    <p style="color:var(--text-secondary); margin-bottom:16px;">You haven't listed any fitness centers yet.</p>
                    <a href="<?= BASE_URL ?>/owner/add-gym.php" class="btn-gz btn-gz-primary btn-gz-sm">
                        <i class="fas fa-plus"></i> List Your First Gym
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

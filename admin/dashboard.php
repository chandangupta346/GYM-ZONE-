<?php
// admin/dashboard.php - Platform Administration Overview
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireRole('admin');
$db = getDB();
$user = currentUser();

// Handle instant approval/rejection from overview queue
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_gym'])) {
    $gymId  = (int)$_POST['gym_id'];
    $status = $_POST['action_status'];
    if (in_array($status, ['approved', 'rejected'])) {
        try {
            $stmt = $db->prepare("UPDATE gyms SET status = ? WHERE id = ?");
            $stmt->execute([$status, $gymId]);
            setFlash('success', "Gym listing #{$gymId} marked as " . strtoupper($status));
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
    }
}

// Fetch Global Metrics
try {
    $totalGyms = $db->query("SELECT COUNT(*) FROM gyms")->fetchColumn();
    $pendingGyms = $db->query("SELECT COUNT(*) FROM gyms WHERE status = 'pending'")->fetchColumn();
    $totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalBookings = $db->query("SELECT COUNT(*) FROM trial_bookings")->fetchColumn();
    $totalReviews = $db->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

    // Pending Gyms Queue
    $pendingStmt = $db->query("SELECT g.*, u.name as owner_name, u.email as owner_email 
                              FROM gyms g 
                              JOIN users u ON g.owner_id = u.id 
                              WHERE g.status = 'pending' 
                              ORDER BY g.created_at ASC");
    $pendingQueue = $pendingStmt->fetchAll();

    // Latest Platform Bookings
    $latestBookingsStmt = $db->query("SELECT b.*, g.name as gym_name, g.area 
                                      FROM trial_bookings b 
                                      JOIN gyms g ON b.gym_id = g.id 
                                      ORDER BY b.created_at DESC LIMIT 6");
    $latestBookings = $latestBookingsStmt->fetchAll();
} catch (Exception $e) {
    die("Admin DB Error: " . $e->getMessage());
}

$pageTitle = "Admin Control Center | GYMZONE";
$extraCss = "dashboard.css";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="dashboard-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <main class="dashboard-content">
        <?= renderFlashMessages() ?>

        <div class="dash-header">
            <div class="dash-header-title">
                <h2>GYMZONE Master Administration</h2>
                <p>Platform ecosystem analytics, gym approvals, and global bookings across Jamshedpur.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/gyms.php" class="btn-gz btn-gz-primary">
                <i class="fas fa-building"></i> Manage All Gyms
            </a>
        </div>

        <!-- Global Metrics -->
        <div class="metrics-row">
            <div class="metric-card">
                <div class="metric-card-info">
                    <h4>Total Gyms</h4>
                    <div class="metric-num"><?= (int)$totalGyms ?></div>
                </div>
                <div class="metric-icon icon-orange">
                    <i class="fas fa-dumbbell"></i>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-card-info">
                    <h4>Pending Approvals</h4>
                    <div class="metric-num" style="<?= ($pendingGyms > 0) ? 'color:var(--warning);' : '' ?>"><?= (int)$pendingGyms ?></div>
                </div>
                <div class="metric-icon icon-blue">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-card-info">
                    <h4>Registered Accounts</h4>
                    <div class="metric-num"><?= (int)$totalUsers ?></div>
                </div>
                <div class="metric-icon icon-green">
                    <i class="fas fa-users"></i>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-card-info">
                    <h4>Trial Visits Booked</h4>
                    <div class="metric-num"><?= (int)$totalBookings ?></div>
                </div>
                <div class="metric-icon icon-purple">
                    <i class="fas fa-ticket-alt"></i>
                </div>
            </div>
        </div>

        <!-- Pending Gym Listings Approval Queue -->
        <?php if (!empty($pendingQueue)): ?>
            <div class="dash-card" style="border:1px solid var(--warning);">
                <div class="dash-card-header">
                    <h3 style="color:#fbbf24;"><i class="fas fa-exclamation-circle text-warning"></i> Pending Gym Approval Queue (<?= count($pendingQueue) ?>)</h3>
                </div>

                <div class="table-responsive">
                    <table class="gz-table">
                        <thead>
                            <tr>
                                <th>Gym Name &amp; Area</th>
                                <th>Owner</th>
                                <th>Price</th>
                                <th>Submitted On</th>
                                <th>Moderation Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingQueue as $pg): ?>
                                <tr>
                                    <td>
                                        <strong style="color:#fff; display:block;"><?= htmlspecialchars($pg['name']) ?></strong>
                                        <small style="color:var(--text-secondary);"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($pg['area']) ?></small>
                                    </td>
                                    <td>
                                        <span style="color:#fff;"><?= htmlspecialchars($pg['owner_name']) ?></span><br>
                                        <small style="color:var(--text-muted);"><?= htmlspecialchars($pg['owner_email']) ?></small>
                                    </td>
                                    <td><?= formatPrice($pg['starting_price']) ?>/mo</td>
                                    <td><?= date('d M Y', strtotime($pg['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline-flex; gap:8px;">
                                            <input type="hidden" name="gym_id" value="<?= $pg['id'] ?>">
                                            <button type="submit" name="action_gym" value="1" onclick="this.form.action_status.value='approved'" class="btn-gz btn-gz-primary btn-gz-sm">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="submit" name="action_gym" value="1" onclick="this.form.action_status.value='rejected'" class="btn-gz btn-gz-secondary btn-gz-sm" style="color:var(--danger);">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                            <input type="hidden" name="action_status" value="">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Latest Bookings across all gyms -->
        <div class="dash-card">
            <div class="dash-card-header">
                <h3><i class="fas fa-ticket-alt text-primary"></i> Master Free Trial Bookings Feed</h3>
                <a href="<?= BASE_URL ?>/admin/bookings.php" class="btn-gz btn-gz-outline btn-gz-sm">View All Bookings</a>
            </div>

            <div class="table-responsive">
                <table class="gz-table">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>Visitor</th>
                            <th>Fitness Center</th>
                            <th>Scheduled Date</th>
                            <th>Slot</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestBookings as $b): ?>
                            <tr>
                                <td><strong style="color:var(--primary); font-family:monospace;"><?= htmlspecialchars($b['booking_ref']) ?></strong></td>
                                <td>
                                    <strong style="color:#fff;"><?= htmlspecialchars($b['user_name']) ?></strong><br>
                                    <small style="color:var(--text-muted);"><?= htmlspecialchars($b['user_phone']) ?></small>
                                </td>
                                <td>
                                    <span style="color:#fff; font-weight:600;"><?= htmlspecialchars($b['gym_name']) ?></span>
                                    <small style="color:var(--text-secondary); display:block;"><?= htmlspecialchars($b['area']) ?></small>
                                </td>
                                <td><?= date('d M Y', strtotime($b['visit_date'])) ?></td>
                                <td><i class="far fa-clock text-warning"></i> <?= htmlspecialchars($b['time_slot']) ?></td>
                                <td>
                                    <?php 
                                    $st = $b['status'];
                                    $pillClass = ($st === 'confirmed') ? 'status-confirmed' : (($st === 'attended') ? 'status-attended' : (($st === 'cancelled') ? 'status-cancelled' : 'status-pending'));
                                    ?>
                                    <span class="status-pill <?= $pillClass ?>">
                                        <?= strtoupper($st) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

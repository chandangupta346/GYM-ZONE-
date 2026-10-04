<?php
// user/bookings.php - User's Free Trial Booking History
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireLogin();
$db = getDB();
$user = currentUser();

// Handle Cancellation
if (isset($_POST['cancel_booking'])) {
    $bId = (int)$_POST['booking_id'];
    try {
        $stmt = $db->prepare("UPDATE trial_bookings SET status = 'cancelled' WHERE id = ? AND (user_id = ? OR user_email = ?)");
        $stmt->execute([$bId, $user['id'], $user['email']]);
        setFlash('info', 'Your trial session booking has been cancelled.');
    } catch (Exception $e) {
        setFlash('error', 'Error updating booking: ' . $e->getMessage());
    }
    header('Location: ' . BASE_URL . '/user/bookings.php');
    exit;
}

// Fetch bookings
try {
    $stmt = $db->prepare("SELECT b.*, g.name as gym_name, g.area, g.address as gym_address, g.phone as gym_phone 
                          FROM trial_bookings b 
                          JOIN gyms g ON b.gym_id = g.id 
                          WHERE b.user_id = ? OR b.user_email = ? 
                          ORDER BY b.created_at DESC");
    $stmt->execute([$user['id'], $user['email']]);
    $bookings = $stmt->fetchAll();
} catch (Exception $e) {
    $bookings = [];
}

$pageTitle = "My Free Trial Bookings | GYMZONE";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="gym-container" style="padding:40px 24px; min-height:75vh;">
    <?= renderFlashMessages() ?>

    <div class="section-header">
        <div class="section-title-wrap">
            <span class="subheading"><i class="fas fa-ticket-alt text-success"></i> Appointments &amp; Passes</span>
            <h1 class="section-title">My Trial Bookings</h1>
            <p class="section-desc">Manage your scheduled gym visits and view digital VIP trial passes.</p>
        </div>
        <a href="javascript:void(0)" class="btn-gz btn-gz-primary open-booking-modal">
            <i class="fas fa-plus"></i> Book Another Trial
        </a>
    </div>

    <?php if (!empty($bookings)): ?>
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); overflow:hidden; box-shadow:var(--shadow-card);">
            <div class="table-responsive">
                <table class="gz-table">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>Fitness Center</th>
                            <th>Scheduled Date &amp; Slot</th>
                            <th>Fitness Goal</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--primary); font-family:monospace; font-size:14px;">
                                        <?= htmlspecialchars($b['booking_ref']) ?>
                                    </strong>
                                    <small style="display:block; color:var(--text-muted); font-size:11px;">
                                        Booked on <?= date('d M Y', strtotime($b['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <strong style="color:#fff; display:block;"><?= htmlspecialchars($b['gym_name']) ?></strong>
                                    <small style="color:var(--text-secondary);"><i class="fas fa-map-marker-alt text-primary"></i> <?= htmlspecialchars($b['area']) ?>, Jamshedpur</small>
                                </td>
                                <td>
                                    <div style="color:#fff; font-weight:600;"><i class="far fa-calendar-alt text-primary"></i> <?= date('d M Y', strtotime($b['visit_date'])) ?></div>
                                    <small style="color:var(--text-muted);"><i class="far fa-clock text-warning"></i> <?= htmlspecialchars($b['time_slot']) ?></small>
                                </td>
                                <td>
                                    <span style="font-size:13px; color:var(--text-secondary);"><?= htmlspecialchars($b['fitness_goal'] ?? 'General') ?></span>
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
                                    <div class="table-actions">
                                        <a href="<?= BASE_URL ?>/gym-details.php?id=<?= $b['gym_id'] ?>" class="btn-icon" title="View Gym Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($b['status'] === 'confirmed' || $b['status'] === 'pending'): ?>
                                            <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this trial booking?');" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                                <button type="submit" name="cancel_booking" class="btn-icon btn-icon-danger" title="Cancel Booking">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:60px 30px; text-align:center; max-width:600px; margin:40px auto;">
            <div style="width:70px; height:70px; background:rgba(0,230,118,0.1); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:var(--success); font-size:32px; margin-bottom:18px;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h3 style="font-size:24px; color:#fff; margin-bottom:8px;">No Trial Bookings Found</h3>
            <p style="color:var(--text-secondary); font-size:14px; margin-bottom:24px;">
                You haven't booked any free gym trial passes yet. Claim a free visit to experience premium equipment in Jamshedpur with zero commitment!
            </p>
            <a href="<?= BASE_URL ?>/explore.php" class="btn-gz btn-gz-primary">
                <i class="fas fa-ticket-alt"></i> Find Gyms &amp; Book Free Trial
            </a>
        </div>
    <?php endif; ?>
</div>

<?php 
include __DIR__ . '/../includes/booking_modal.php';
include __DIR__ . '/../includes/footer.php'; 
?>

<?php
// owner/bookings.php - Gym Owner Trial Bookings Management
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireRole(['owner', 'admin']);
$db = getDB();
$user = currentUser();

// Handle Status or Note Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_booking'])) {
    $bookingId = (int)$_POST['booking_id'];
    $newStatus = $_POST['status'];
    $notes     = trim($_POST['owner_notes'] ?? '');

    try {
        $stmt = $db->prepare("UPDATE trial_bookings b 
                              JOIN gyms g ON b.gym_id = g.id 
                              SET b.status = ?, b.owner_notes = ? 
                              WHERE b.id = ? AND (g.owner_id = ? OR ? = 'admin')");
        $stmt->execute([$newStatus, $notes, $bookingId, $user['id'], $user['role']]);
        setFlash('success', 'Booking updated successfully.');
    } catch (Exception $e) {
        setFlash('error', 'Error updating booking: ' . $e->getMessage());
    }
    header('Location: ' . BASE_URL . '/owner/bookings.php');
    exit;
}

// Filters
$filterGym    = (int)($_GET['gym_id'] ?? 0);
$filterStatus = $_GET['status'] ?? '';

// Build Query
$sql = "SELECT b.*, g.name as gym_name, g.area 
        FROM trial_bookings b 
        JOIN gyms g ON b.gym_id = g.id 
        WHERE (g.owner_id = ? OR ? = 'admin')";
$params = [$user['id'], $user['role']];

if ($filterGym > 0) {
    $sql .= " AND b.gym_id = ?";
    $params[] = $filterGym;
}
if (!empty($filterStatus)) {
    $sql .= " AND b.status = ?";
    $params[] = $filterStatus;
}
$sql .= " ORDER BY b.visit_date DESC, b.created_at DESC";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    // Owner gyms for dropdown
    $gymsStmt = $db->prepare("SELECT id, name FROM gyms WHERE owner_id = ? OR ? = 'admin' ORDER BY name ASC");
    $gymsStmt->execute([$user['id'], $user['role']]);
    $myGyms = $gymsStmt->fetchAll();
} catch (Exception $e) {
    $bookings = [];
    $myGyms = [];
}

$pageTitle = "Trial Bookings Management | GYMZONE Owner Portal";
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
                <h2>Manage Free Trial Bookings</h2>
                <p>Verify visitor appointments, check attendances, and coordinate trial sessions.</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="dash-card" style="padding:16px 24px; margin-bottom:24px;">
            <form action="<?= BASE_URL ?>/owner/bookings.php" method="GET" style="display:flex; flex-wrap:wrap; gap:16px; align-items:center;">
                <div style="flex:1; min-width:200px;">
                    <select name="gym_id" class="gz-select" onchange="this.form.submit()">
                        <option value="">All My Gyms</option>
                        <?php foreach ($myGyms as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= ($filterGym === $g['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="width:200px;">
                    <select name="status" class="gz-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= ($filterStatus === 'pending') ? 'selected' : '' ?>>Pending Review</option>
                        <option value="confirmed" <?= ($filterStatus === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                        <option value="attended" <?= ($filterStatus === 'attended') ? 'selected' : '' ?>>Attended Session</option>
                        <option value="cancelled" <?= ($filterStatus === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <?php if ($filterGym > 0 || !empty($filterStatus)): ?>
                    <a href="<?= BASE_URL ?>/owner/bookings.php" class="btn-gz btn-gz-outline btn-gz-sm">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="dash-card">
            <?php if (!empty($bookings)): ?>
                <div class="table-responsive">
                    <table class="gz-table">
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Visitor Info</th>
                                <th>Gym Center</th>
                                <th>Scheduled Slot</th>
                                <th>Fitness Goal</th>
                                <th>Status</th>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td>
                                        <strong style="color:var(--primary); font-family:monospace;"><?= htmlspecialchars($b['booking_ref']) ?></strong>
                                        <small style="display:block; color:var(--text-muted); font-size:11px;"><?= date('d M Y', strtotime($b['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <strong style="color:#fff; display:block;"><?= htmlspecialchars($b['user_name']) ?></strong>
                                        <div style="font-size:12px; color:var(--text-secondary);"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($b['user_phone']) ?></div>
                                        <div style="font-size:12px; color:var(--text-muted);"><i class="fas fa-envelope"></i> <?= htmlspecialchars($b['user_email']) ?></div>
                                    </td>
                                    <td>
                                        <span style="color:#fff; font-weight:600;"><?= htmlspecialchars($b['gym_name']) ?></span>
                                    </td>
                                    <td>
                                        <div style="color:#fff; font-weight:600;"><?= date('d M Y', strtotime($b['visit_date'])) ?></div>
                                        <small style="color:var(--text-warning);"><i class="far fa-clock"></i> <?= htmlspecialchars($b['time_slot']) ?></small>
                                    </td>
                                    <td>
                                        <span style="font-size:13px; color:var(--text-secondary);"><?= htmlspecialchars($b['fitness_goal']) ?></span>
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
                                        <form method="POST" style="display:flex; flex-direction:column; gap:6px;">
                                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                            <select name="status" class="gz-select" style="padding:4px 8px; font-size:12px;">
                                                <option value="pending" <?= ($st === 'pending') ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= ($st === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="attended" <?= ($st === 'attended') ? 'selected' : '' ?>>Attended</option>
                                                <option value="cancelled" <?= ($st === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_booking" class="btn-gz btn-gz-primary btn-gz-sm" style="padding:4px 8px; font-size:11px;">
                                                Save
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color:var(--text-muted); font-size:14px; text-align:center; padding:40px;">
                    No trial bookings match your current filter selection.
                </p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

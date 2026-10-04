<?php
// admin/bookings.php - Master Bookings Administration
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireRole('admin');
$db = getDB();

// Handle Status & Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $bId = (int)$_POST['booking_id'];
        $st  = $_POST['status'];
        if (in_array($st, ['pending', 'confirmed', 'attended', 'cancelled'])) {
            $stmt = $db->prepare("UPDATE trial_bookings SET status = ? WHERE id = ?");
            $stmt->execute([$st, $bId]);
            setFlash('success', 'Booking status updated.');
        }
    } elseif (isset($_POST['delete_booking'])) {
        $bId = (int)$_POST['booking_id'];
        $stmt = $db->prepare("DELETE FROM trial_bookings WHERE id = ?");
        $stmt->execute([$bId]);
        setFlash('info', 'Booking record deleted.');
    }
    header('Location: ' . BASE_URL . '/admin/bookings.php');
    exit;
}

// Filters
$filterGym = (int)($_GET['gym_id'] ?? 0);
$filterStatus = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT b.*, g.name as gym_name, g.area 
        FROM trial_bookings b 
        JOIN gyms g ON b.gym_id = g.id";
$where = [];
$params = [];

if ($filterGym > 0) {
    $where[] = "b.gym_id = ?";
    $params[] = $filterGym;
}
if (!empty($filterStatus)) {
    $where[] = "b.status = ?";
    $params[] = $filterStatus;
}
if (!empty($search)) {
    $where[] = "(b.booking_ref LIKE ? OR b.user_name LIKE ? OR b.user_phone LIKE ?)";
    $t = "%$search%";
    $params[] = $t; $params[] = $t; $params[] = $t;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY b.visit_date DESC, b.created_at DESC";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    $gymsList = $db->query("SELECT id, name FROM gyms ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    $bookings = [];
    $gymsList = [];
}

$pageTitle = "Master Trial Bookings Log | GYMZONE Admin";
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
                <h2>Master Trial Bookings Log (<?= count($bookings) ?>)</h2>
                <p>Global monitor of all free gym trial reservations across Jamshedpur.</p>
            </div>
        </div>

        <div class="dash-card" style="padding:16px 24px; margin-bottom:24px;">
            <form action="<?= BASE_URL ?>/admin/bookings.php" method="GET" style="display:flex; flex-wrap:wrap; gap:14px; align-items:center;">
                <input type="text" name="search" class="gz-input" placeholder="Search ref, name, mobile..." value="<?= htmlspecialchars($search) ?>" style="flex:1; min-width:200px;">

                <select name="gym_id" class="gz-select" style="width:220px;" onchange="this.form.submit()">
                    <option value="">All Gyms in Jamshedpur</option>
                    <?php foreach ($gymsList as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= ($filterGym === $g['id']) ? 'selected' : '' ?>><?= htmlspecialchars($g['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="status" class="gz-select" style="width:160px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= ($filterStatus === 'pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= ($filterStatus === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                    <option value="attended" <?= ($filterStatus === 'attended') ? 'selected' : '' ?>>Attended</option>
                    <option value="cancelled" <?= ($filterStatus === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                </select>

                <button type="submit" class="btn-gz btn-gz-primary btn-gz-sm">Filter</button>
            </form>
        </div>

        <div class="dash-card">
            <?php if (!empty($bookings)): ?>
                <div class="table-responsive">
                    <table class="gz-table">
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Visitor Information</th>
                                <th>Gym Facility</th>
                                <th>Visit Date &amp; Slot</th>
                                <th>Status</th>
                                <th>Change Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td>
                                        <strong style="color:var(--primary); font-family:monospace;"><?= htmlspecialchars($b['booking_ref']) ?></strong>
                                    </td>
                                    <td>
                                        <strong style="color:#fff;"><?= htmlspecialchars($b['user_name']) ?></strong><br>
                                        <small style="color:var(--text-secondary);"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($b['user_phone']) ?></small>
                                    </td>
                                    <td>
                                        <span style="color:#fff; font-weight:600;"><?= htmlspecialchars($b['gym_name']) ?></span>
                                        <small style="display:block; color:var(--text-muted);"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($b['area']) ?></small>
                                    </td>
                                    <td>
                                        <div style="color:#fff; font-weight:600;"><?= date('d M Y', strtotime($b['visit_date'])) ?></div>
                                        <small style="color:var(--text-warning);"><i class="far fa-clock"></i> <?= htmlspecialchars($b['time_slot']) ?></small>
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
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                            <select name="status" class="gz-select" style="padding:4px 8px; font-size:12px; width:120px;" onchange="this.form.submit()">
                                                <option value="pending" <?= ($st === 'pending') ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= ($st === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="attended" <?= ($st === 'attended') ? 'selected' : '' ?>>Attended</option>
                                                <option value="cancelled" <?= ($st === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Delete this booking record?');" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                            <button type="submit" name="delete_booking" class="btn-icon btn-icon-danger" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color:var(--text-muted); font-size:14px; text-align:center; padding:40px;">No trial session bookings found.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

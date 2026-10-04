<?php
// admin/gyms.php - Master Gyms Directory & Moderation
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireRole('admin');
$db = getDB();
$user = currentUser();

// Handle Status & Featured Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $gymId = (int)$_POST['gym_id'];
        $status = $_POST['status'];
        if (in_array($status, ['approved', 'pending', 'rejected'])) {
            $stmt = $db->prepare("UPDATE gyms SET status = ? WHERE id = ?");
            $stmt->execute([$status, $gymId]);
            setFlash('success', "Gym #{$gymId} status changed to " . strtoupper($status));
        }
    } elseif (isset($_POST['toggle_featured'])) {
        $gymId = (int)$_POST['gym_id'];
        $newFeatured = (int)$_POST['featured_val'] ? 0 : 1;
        $stmt = $db->prepare("UPDATE gyms SET is_featured = ? WHERE id = ?");
        $stmt->execute([$newFeatured, $gymId]);
        setFlash('success', "Gym #{$gymId} featured status updated.");
    } elseif (isset($_POST['delete_gym'])) {
        $gymId = (int)$_POST['gym_id'];
        $stmt = $db->prepare("DELETE FROM gyms WHERE id = ?");
        $stmt->execute([$gymId]);
        setFlash('info', "Gym #{$gymId} deleted from database.");
    }
    header('Location: ' . BASE_URL . '/admin/gyms.php');
    exit;
}

// Fetch all gyms with owner info
try {
    $stmt = $db->query("SELECT g.*, u.name as owner_name, u.email as owner_email 
                        FROM gyms g 
                        LEFT JOIN users u ON g.owner_id = u.id 
                        ORDER BY g.created_at DESC");
    $gyms = $stmt->fetchAll();
} catch (Exception $e) {
    $gyms = [];
}

$pageTitle = "Manage All Gyms | GYMZONE Admin";
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
                <h2>Gyms Directory &amp; Moderation (<?= count($gyms) ?>)</h2>
                <p>Approve listings, promote featured gyms, or edit gym metadata.</p>
            </div>
            <a href="<?= BASE_URL ?>/owner/add-gym.php" class="btn-gz btn-gz-primary">
                <i class="fas fa-plus"></i> Add New Gym
            </a>
        </div>

        <div class="dash-card">
            <div class="table-responsive">
                <table class="gz-table">
                    <thead>
                        <tr>
                            <th>Gym Name &amp; Area</th>
                            <th>Owner Contact</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gyms as $g): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <img src="<?= htmlspecialchars($g['main_image']) ?>" alt="<?= htmlspecialchars($g['name']) ?>" style="width:44px; height:44px; border-radius:6px; object-fit:cover;">
                                        <div>
                                            <strong style="color:#fff; display:block;"><?= htmlspecialchars($g['name']) ?></strong>
                                            <small style="color:var(--text-secondary);"><i class="fas fa-map-marker-alt text-primary"></i> <?= htmlspecialchars($g['area']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="color:#fff; font-size:13px;"><?= htmlspecialchars($g['owner_name'] ?? 'System') ?></span><br>
                                    <small style="color:var(--text-muted); font-size:11px;"><?= htmlspecialchars($g['owner_email'] ?? '') ?></small>
                                </td>
                                <td>
                                    <strong style="color:var(--primary);"><?= formatPrice($g['starting_price']) ?></strong>
                                </td>
                                <td>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="gym_id" value="<?= $g['id'] ?>">
                                        <select name="status" class="gz-select" style="padding:4px 8px; font-size:12px;" onchange="this.form.submit()">
                                            <option value="approved" <?= ($g['status'] === 'approved') ? 'selected' : '' ?>>Approved</option>
                                            <option value="pending" <?= ($g['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                                            <option value="rejected" <?= ($g['status'] === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="gym_id" value="<?= $g['id'] ?>">
                                        <input type="hidden" name="featured_val" value="<?= $g['is_featured'] ?>">
                                        <button type="submit" name="toggle_featured" class="btn-gz btn-gz-sm <?= $g['is_featured'] ? 'btn-gz-primary' : 'btn-gz-secondary' ?>" style="padding:4px 10px; font-size:11px;">
                                            <i class="fas fa-crown"></i> <?= $g['is_featured'] ? 'Featured' : 'Standard' ?>
                                        </button>
                                    </form>
                                </td>
                                <td><?= renderStars($g['rating'], true) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= BASE_URL ?>/gym-details.php?id=<?= $g['id'] ?>" target="_blank" class="btn-icon" title="View Public Page"><i class="fas fa-eye"></i></a>
                                        <a href="<?= BASE_URL ?>/owner/edit-gym.php?id=<?= $g['id'] ?>" class="btn-icon" title="Edit Gym Details"><i class="fas fa-edit"></i></a>
                                        <form method="POST" onsubmit="return confirm('Permanently delete this gym listing?');" style="display:inline;">
                                            <input type="hidden" name="gym_id" value="<?= $g['id'] ?>">
                                            <button type="submit" name="delete_gym" class="btn-icon btn-icon-danger" title="Delete Gym">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
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

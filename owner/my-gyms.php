<?php
// owner/my-gyms.php - Gym Owner's Gym Catalog Management
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireRole(['owner', 'admin']);
$db = getDB();
$user = currentUser();

// Handle Delete Request
if (isset($_POST['delete_gym'])) {
    $gymId = (int)$_POST['gym_id'];
    try {
        $stmt = $db->prepare("DELETE FROM gyms WHERE id = ? AND (owner_id = ? OR ? = 'admin')");
        $stmt->execute([$gymId, $user['id'], $user['role']]);
        setFlash('success', 'Gym listing deleted successfully.');
    } catch (Exception $e) {
        setFlash('error', 'Error deleting gym: ' . $e->getMessage());
    }
    header('Location: ' . BASE_URL . '/owner/my-gyms.php');
    exit;
}

// Fetch all gyms for this owner
try {
    $stmt = $db->prepare("SELECT * FROM gyms WHERE owner_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $gyms = $stmt->fetchAll();
} catch (Exception $e) {
    $gyms = [];
}

$pageTitle = "My Gym Listings | GYMZONE Owner Portal";
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
                <h2>My Gym Listings (<?= count($gyms) ?>)</h2>
                <p>Manage your fitness centers, facilities, photos, and membership rates.</p>
            </div>
            <a href="<?= BASE_URL ?>/owner/add-gym.php" class="btn-gz btn-gz-primary">
                <i class="fas fa-plus"></i> Add New Gym
            </a>
        </div>

        <?php if (!empty($gyms)): ?>
            <div class="dash-card">
                <div class="table-responsive">
                    <table class="gz-table">
                        <thead>
                            <tr>
                                <th>Fitness Center</th>
                                <th>Area / Locality</th>
                                <th>Monthly Fee</th>
                                <th>Opening Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gyms as $gym): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            <img src="<?= htmlspecialchars($gym['main_image']) ?>" alt="<?= htmlspecialchars($gym['name']) ?>" style="width:50px; height:50px; border-radius:8px; object-fit:cover;">
                                            <div>
                                                <strong style="color:#fff; display:block;"><?= htmlspecialchars($gym['name']) ?></strong>
                                                <small style="color:var(--text-muted);"><?= htmlspecialchars($gym['phone']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="color:#fff;"><i class="fas fa-map-marker-alt text-primary"></i> <?= htmlspecialchars($gym['area']) ?></span>
                                    </td>
                                    <td>
                                        <strong style="color:var(--primary);"><?= formatPrice($gym['starting_price']) ?></strong>
                                    </td>
                                    <td>
                                        <span style="font-size:13px;"><?= date('d M Y', strtotime($gym['opening_date'])) ?></span>
                                        <?php if ($gym['is_newly_opened'] || isNewlyOpened($gym['opening_date'])): ?>
                                            <span class="badge-new-nav">NEW</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-pill <?= ($gym['status'] === 'approved') ? 'status-approved' : (($gym['status'] === 'pending') ? 'status-pending' : 'status-rejected') ?>">
                                            <?= strtoupper($gym['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="<?= BASE_URL ?>/gym-details.php?id=<?= $gym['id'] ?>" class="btn-icon" title="View Public Page"><i class="fas fa-eye"></i></a>
                                            <a href="<?= BASE_URL ?>/owner/edit-gym.php?id=<?= $gym['id'] ?>" class="btn-icon" title="Edit Listing"><i class="fas fa-edit"></i></a>
                                            <a href="<?= BASE_URL ?>/owner/plans.php?gym_id=<?= $gym['id'] ?>" class="btn-icon" title="Membership Plans"><i class="fas fa-tags"></i></a>
                                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this gym listing? This cannot be undone.');" style="display:inline;">
                                                <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                                                <button type="submit" name="delete_gym" class="btn-icon btn-icon-danger" title="Delete Listing">
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
        <?php else: ?>
            <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:60px 30px; text-align:center;">
                <div style="width:70px; height:70px; background:rgba(255,87,34,0.1); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:var(--primary); font-size:32px; margin-bottom:18px;">
                    <i class="fas fa-building"></i>
                </div>
                <h3 style="font-size:24px; color:#fff; margin-bottom:8px;">No Gyms Registered Yet</h3>
                <p style="color:var(--text-secondary); font-size:14px; margin-bottom:24px;">
                    Click below to create your first fitness center listing and showcase your equipment to Jamshedpur athletes.
                </p>
                <a href="<?= BASE_URL ?>/owner/add-gym.php" class="btn-gz btn-gz-primary">
                    <i class="fas fa-plus"></i> Add New Gym Listing
                </a>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

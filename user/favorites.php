<?php
// user/favorites.php - Saved Favorite Gyms
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireLogin();
$db = getDB();
$user = currentUser();

// Handle removal if clicked directly
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    try {
        $delStmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND gym_id = ?");
        $delStmt->execute([$user['id'], $removeId]);
        setFlash('info', 'Removed gym from your favorites.');
        header('Location: ' . BASE_URL . '/user/favorites.php');
        exit;
    } catch (Exception $e) {}
}

// Fetch user favorites
try {
    $stmt = $db->prepare("SELECT g.* FROM gyms g 
                          JOIN favorites f ON g.id = f.gym_id 
                          WHERE f.user_id = ? 
                          ORDER BY f.created_at DESC");
    $stmt->execute([$user['id']]);
    $favoriteGyms = $stmt->fetchAll();
} catch (Exception $e) {
    $favoriteGyms = [];
}

$pageTitle = "My Saved Gyms | GYMZONE";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="gym-container" style="padding:40px 24px; min-height:75vh;">
    <?= renderFlashMessages() ?>

    <div class="section-header">
        <div class="section-title-wrap">
            <span class="subheading"><i class="fas fa-heart text-danger"></i> Saved For Later</span>
            <h1 class="section-title">My Favorite Gyms</h1>
            <p class="section-desc">Quickly access and compare your bookmarked fitness centers in Jamshedpur.</p>
        </div>
        <a href="<?= BASE_URL ?>/explore.php" class="btn-gz btn-gz-outline">
            <i class="fas fa-search"></i> Explore More Gyms
        </a>
    </div>

    <?php if (!empty($favoriteGyms)): ?>
        <div class="gyms-grid">
            <?php foreach ($favoriteGyms as $gym): ?>
                <?php include __DIR__ . '/../includes/gym_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:60px 30px; text-align:center; max-width:600px; margin:40px auto;">
            <div style="width:70px; height:70px; background:rgba(255,82,82,0.1); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:var(--danger); font-size:32px; margin-bottom:18px;">
                <i class="far fa-heart"></i>
            </div>
            <h3 style="font-size:24px; color:#fff; margin-bottom:8px;">No Saved Gyms Yet</h3>
            <p style="color:var(--text-secondary); font-size:14px; margin-bottom:24px;">
                Tap the heart icon on any gym card while browsing to save fitness centers here for quick comparison and booking.
            </p>
            <a href="<?= BASE_URL ?>/explore.php" class="btn-gz btn-gz-primary">
                <i class="fas fa-compass"></i> Discover Gyms in Jamshedpur
            </a>
        </div>
    <?php endif; ?>
</div>

<?php 
include __DIR__ . '/../includes/booking_modal.php';
include __DIR__ . '/../includes/footer.php'; 
?>

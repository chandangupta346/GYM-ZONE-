<?php
// user/profile.php - User Profile & Account Settings
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireLogin();
$db = getDB();
$user = currentUser();

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name   = trim($_POST['name'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $avatar = trim($_POST['avatar'] ?? '');

    if (!empty($name)) {
        try {
            $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $avatar, $user['id']]);

            $_SESSION['user_name'] = $name;
            $_SESSION['user_avatar'] = !empty($avatar) ? $avatar : $user['avatar'];
            setFlash('success', 'Profile updated successfully!');
            $user = currentUser();
        } catch (Exception $e) {
            setFlash('error', 'Update error: ' . $e->getMessage());
        }
    } else {
        setFlash('error', 'Name cannot be empty.');
    }
}

// Fetch stats for user
try {
    $favCountStmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
    $favCountStmt->execute([$user['id']]);
    $favCount = $favCountStmt->fetchColumn();

    $bookCountStmt = $db->prepare("SELECT COUNT(*) FROM trial_bookings WHERE user_id = ? OR user_email = ?");
    $bookCountStmt->execute([$user['id'], $user['email']]);
    $bookCount = $bookCountStmt->fetchColumn();

    $revCountStmt = $db->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ?");
    $revCountStmt->execute([$user['id']]);
    $revCount = $revCountStmt->fetchColumn();
} catch (Exception $e) {
    $favCount = $bookCount = $revCount = 0;
}

$pageTitle = "My Profile &amp; Account | GYMZONE";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="gym-container" style="padding:40px 24px;">
    <?= renderFlashMessages() ?>

    <div style="display:grid; grid-template-columns:300px 1fr; gap:36px; align-items:start;">
        <!-- Left: User Info Card -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:30px; text-align:center;">
            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>" style="width:110px; height:110px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); margin-bottom:16px;">
            <h2 style="font-size:22px; color:#fff; margin-bottom:4px;"><?= htmlspecialchars($user['name']) ?></h2>
            <p style="color:var(--text-muted); font-size:13px; margin-bottom:12px;"><?= htmlspecialchars($user['email']) ?></p>
            <span class="status-pill status-confirmed" style="margin-bottom:20px;">
                <?= strtoupper($user['role']) ?> ACCOUNT
            </span>

            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; border-top:1px solid var(--border-color); border-bottom:1px solid var(--border-color); padding:16px 0; margin-bottom:20px;">
                <div>
                    <strong style="font-family:var(--font-heading); font-size:22px; color:#fff; display:block;"><?= $favCount ?></strong>
                    <small style="color:var(--text-muted); font-size:11px;">Saved Gyms</small>
                </div>
                <div>
                    <strong style="font-family:var(--font-heading); font-size:22px; color:var(--primary); display:block;"><?= $bookCount ?></strong>
                    <small style="color:var(--text-muted); font-size:11px;">Trials</small>
                </div>
                <div>
                    <strong style="font-family:var(--font-heading); font-size:22px; color:var(--warning); display:block;"><?= $revCount ?></strong>
                    <small style="color:var(--text-muted); font-size:11px;">Reviews</small>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="<?= BASE_URL ?>/user/favorites.php" class="btn-gz btn-gz-secondary btn-gz-sm" style="justify-content:flex-start;">
                    <i class="fas fa-heart text-danger"></i> My Saved Gyms
                </a>
                <a href="<?= BASE_URL ?>/user/bookings.php" class="btn-gz btn-gz-secondary btn-gz-sm" style="justify-content:flex-start;">
                    <i class="fas fa-calendar-check text-success"></i> My Trial Bookings
                </a>
                <?php if ($user['role'] === 'owner'): ?>
                    <a href="<?= BASE_URL ?>/owner/dashboard.php" class="btn-gz btn-gz-primary btn-gz-sm" style="justify-content:flex-start;">
                        <i class="fas fa-store-alt"></i> Gym Owner Portal
                    </a>
                <?php endif; ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn-gz btn-gz-primary btn-gz-sm" style="justify-content:flex-start;">
                        <i class="fas fa-shield-alt"></i> Admin Console
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Edit Profile Settings -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:36px;">
            <div style="border-bottom:1px solid var(--border-color); padding-bottom:16px; margin-bottom:24px;">
                <h3 style="font-size:22px; color:#fff; margin-bottom:4px;">Account Settings</h3>
                <p style="color:var(--text-secondary); font-size:14px;">Update your personal profile information</p>
            </div>

            <form action="<?= BASE_URL ?>/user/profile.php" method="POST">
                <div class="gz-form-group">
                    <label class="gz-label" for="userName">Full Name</label>
                    <input type="text" name="name" id="userName" class="gz-input" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="userEmail">Email Address (Read-only)</label>
                    <input type="email" id="userEmail" class="gz-input" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:0.6; cursor:not-allowed;">
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="userPhone">Mobile Phone</label>
                    <input type="tel" name="phone" id="userPhone" class="gz-input" value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '+91 98351 77665') ?>" placeholder="+91 98765 43210">
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="userAvatar">Avatar Image URL</label>
                    <input type="url" name="avatar" id="userAvatar" class="gz-input" value="<?= htmlspecialchars($user['avatar']) ?>" placeholder="https://example.com/avatar.jpg">
                    <small style="color:var(--text-muted); font-size:11px; margin-top:4px; display:block;">Enter any image URL to personalize your profile picture.</small>
                </div>

                <button type="submit" name="update_profile" class="btn-gz btn-gz-primary" style="margin-top:10px;">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

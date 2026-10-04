<?php
// admin/users.php - User & Gym Owner Account Administration
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireRole('admin');
$db = getDB();
$currentUser = currentUser();

// Handle Role Change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_role'])) {
        $targetId = (int)$_POST['user_id'];
        $newRole  = $_POST['role'];
        if (in_array($newRole, ['user', 'owner', 'admin']) && $targetId !== $currentUser['id']) {
            $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $targetId]);
            setFlash('success', 'User role updated.');
        }
    } elseif (isset($_POST['delete_user'])) {
        $targetId = (int)$_POST['user_id'];
        if ($targetId !== $currentUser['id']) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$targetId]);
            setFlash('info', 'User deleted.');
        } else {
            setFlash('error', 'You cannot delete your own admin account.');
        }
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

// Fetch all users
try {
    $search = trim($_GET['search'] ?? '');
    if (!empty($search)) {
        $stmt = $db->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY created_at DESC");
        $term = "%$search%";
        $stmt->execute([$term, $term, $term]);
    } else {
        $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
    }
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
}

$pageTitle = "Manage Users &amp; Owners | GYMZONE Admin";
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
                <h2>Manage Users &amp; Gym Owners (<?= count($users) ?>)</h2>
                <p>Monitor registered athletes, change account privileges, or inspect profiles.</p>
            </div>
            
            <form action="<?= BASE_URL ?>/admin/users.php" method="GET" style="display:flex; gap:10px;">
                <input type="text" name="search" class="gz-input" placeholder="Search by name, email, phone..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width:260px;">
                <button type="submit" class="btn-gz btn-gz-primary btn-gz-sm">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="dash-card">
            <div class="table-responsive">
                <table class="gz-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email Address</th>
                            <th>Mobile Phone</th>
                            <th>Role &amp; Privilege</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <img src="<?= htmlspecialchars($u['avatar']) ?>" alt="<?= htmlspecialchars($u['name']) ?>" style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                                        <strong style="color:#fff;"><?= htmlspecialchars($u['name']) ?></strong>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['phone'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if ($u['id'] === $currentUser['id']): ?>
                                        <span class="sidebar-role-badge badge-admin">ADMIN (YOU)</span>
                                    <?php else: ?>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <select name="role" class="gz-select" style="padding:4px 8px; font-size:12px; width:130px;" onchange="this.form.submit()">
                                                <option value="user" <?= ($u['role'] === 'user') ? 'selected' : '' ?>>Member</option>
                                                <option value="owner" <?= ($u['role'] === 'owner') ? 'selected' : '' ?>>Gym Owner</option>
                                                <option value="admin" <?= ($u['role'] === 'admin') ? 'selected' : '' ?>>Administrator</option>
                                            </select>
                                            <input type="hidden" name="change_role" value="1">
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <?php if ($u['id'] !== $currentUser['id']): ?>
                                        <form method="POST" onsubmit="return confirm('Delete this user account?');" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" name="delete_user" class="btn-icon btn-icon-danger" title="Delete User">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted); font-size:12px;">Active Session</span>
                                    <?php endif; ?>
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

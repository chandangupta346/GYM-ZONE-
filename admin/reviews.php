<?php
// admin/reviews.php - Customer Reviews Moderation
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireRole('admin');
$db = getDB();

// Handle Status & Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $revId = (int)$_POST['review_id'];
        $st    = $_POST['status'];
        if (in_array($st, ['approved', 'pending', 'hidden'])) {
            $stmt = $db->prepare("UPDATE reviews SET status = ? WHERE id = ?");
            $stmt->execute([$st, $revId]);
            setFlash('success', 'Review status updated.');
        }
    } elseif (isset($_POST['delete_review'])) {
        $revId = (int)$_POST['review_id'];
        $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$revId]);
        setFlash('info', 'Review deleted.');
    }
    header('Location: ' . BASE_URL . '/admin/reviews.php');
    exit;
}

try {
    $stmt = $db->query("SELECT r.*, g.name as gym_name, g.area 
                        FROM reviews r 
                        JOIN gyms g ON r.gym_id = g.id 
                        ORDER BY r.created_at DESC");
    $reviews = $stmt->fetchAll();
} catch (Exception $e) {
    $reviews = [];
}

$pageTitle = "Moderate Reviews | GYMZONE Admin";
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
                <h2>Customer Reviews &amp; Ratings Moderation (<?= count($reviews) ?>)</h2>
                <p>Ensure genuine customer feedback and maintain community review standards.</p>
            </div>
        </div>

        <div class="dash-card">
            <?php if (!empty($reviews)): ?>
                <div class="table-responsive">
                    <table class="gz-table">
                        <thead>
                            <tr>
                                <th>Fitness Center</th>
                                <th>Reviewer</th>
                                <th>Score</th>
                                <th>Comment</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $rev): ?>
                                <tr>
                                    <td>
                                        <strong style="color:#fff;"><?= htmlspecialchars($rev['gym_name']) ?></strong>
                                        <small style="display:block; color:var(--text-muted);"><?= htmlspecialchars($rev['area']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($rev['user_name']) ?></td>
                                    <td><?= renderStars($rev['rating'], true) ?></td>
                                    <td style="max-width:320px;">
                                        <span style="font-size:13px; color:var(--text-secondary);"><?= htmlspecialchars($rev['comment']) ?></span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($rev['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                            <select name="status" class="gz-select" style="padding:4px 8px; font-size:12px; width:110px;" onchange="this.form.submit()">
                                                <option value="approved" <?= ($rev['status'] === 'approved') ? 'selected' : '' ?>>Approved</option>
                                                <option value="pending" <?= ($rev['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                                                <option value="hidden" <?= ($rev['status'] === 'hidden') ? 'selected' : '' ?>>Hidden</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Delete this review?');" style="display:inline;">
                                            <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                            <button type="submit" name="delete_review" class="btn-icon btn-icon-danger" title="Delete">
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
                <p style="color:var(--text-muted); font-size:14px; text-align:center; padding:40px;">No customer reviews submitted yet.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

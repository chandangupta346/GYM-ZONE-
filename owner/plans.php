<?php
// owner/plans.php - Manage Membership Plans
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireRole(['owner', 'admin']);
$db = getDB();
$user = currentUser();

// Fetch Owner's Gyms
try {
    $gymsStmt = $db->prepare("SELECT id, name FROM gyms WHERE owner_id = ? OR ? = 'admin' ORDER BY name ASC");
    $gymsStmt->execute([$user['id'], $user['role']]);
    $myGyms = $gymsStmt->fetchAll();
} catch (Exception $e) {
    $myGyms = [];
}

$selectedGymId = (int)($_GET['gym_id'] ?? ($myGyms[0]['id'] ?? 0));

// Handle Add Plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_plan'])) {
    $gymId       = (int)$_POST['gym_id'];
    $planType    = $_POST['plan_type'];
    $title       = trim($_POST['title']);
    $price       = (float)$_POST['price'];
    $discPrice   = !empty($_POST['discounted_price']) ? (float)$_POST['discounted_price'] : null;
    $features    = trim($_POST['features']);
    $isPopular   = isset($_POST['is_popular']) ? 1 : 0;

    try {
        $stmt = $db->prepare("INSERT INTO membership_plans (gym_id, plan_type, title, price, discounted_price, features, is_popular) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$gymId, $planType, $title, $price, $discPrice, $features, $isPopular]);
        setFlash('success', 'New membership package added!');
    } catch (Exception $e) {
        setFlash('error', 'Error adding plan: ' . $e->getMessage());
    }
    header('Location: ' . BASE_URL . '/owner/plans.php?gym_id=' . $gymId);
    exit;
}

// Handle Delete Plan
if (isset($_POST['delete_plan'])) {
    $planId = (int)$_POST['plan_id'];
    try {
        $stmt = $db->prepare("DELETE p FROM membership_plans p 
                              JOIN gyms g ON p.gym_id = g.id 
                              WHERE p.id = ? AND (g.owner_id = ? OR ? = 'admin')");
        $stmt->execute([$planId, $user['id'], $user['role']]);
        setFlash('info', 'Membership plan deleted.');
    } catch (Exception $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
    }
    header('Location: ' . BASE_URL . '/owner/plans.php?gym_id=' . $selectedGymId);
    exit;
}

// Fetch plans for selected gym
try {
    $plansStmt = $db->prepare("SELECT p.*, g.name as gym_name FROM membership_plans p 
                              JOIN gyms g ON p.gym_id = g.id 
                              WHERE p.gym_id = ? AND (g.owner_id = ? OR ? = 'admin') 
                              ORDER BY p.price ASC");
    $plansStmt->execute([$selectedGymId, $user['id'], $user['role']]);
    $plans = $plansStmt->fetchAll();
} catch (Exception $e) {
    $plans = [];
}

$pageTitle = "Manage Membership Plans | GYMZONE Owner Portal";
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
                <h2>Membership Packages &amp; Pricing</h2>
                <p>Configure subscriptions, early-bird rates, and member perks for your gym.</p>
            </div>
        </div>

        <?php if (!empty($myGyms)): ?>
            <!-- Gym Selector Tab -->
            <div class="dash-card" style="padding:16px 24px; margin-bottom:24px;">
                <form action="<?= BASE_URL ?>/owner/plans.php" method="GET" style="display:flex; align-items:center; gap:14px;">
                    <label class="gz-label" style="margin:0; white-space:nowrap;">Select Gym:</label>
                    <select name="gym_id" class="gz-select" style="max-width:350px;" onchange="this.form.submit()">
                        <?php foreach ($myGyms as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= ($selectedGymId === $g['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1.4fr; gap:30px; align-items:start;">
                <!-- Add Plan Form -->
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h3><i class="fas fa-plus text-primary"></i> Add Membership Plan</h3>
                    </div>

                    <form action="<?= BASE_URL ?>/owner/plans.php" method="POST">
                        <input type="hidden" name="gym_id" value="<?= $selectedGymId ?>">

                        <div class="gz-form-group">
                            <label class="gz-label" for="planType">Billing Cycle *</label>
                            <select name="plan_type" id="planType" class="gz-select" required>
                                <option value="Monthly">Monthly</option>
                                <option value="Quarterly">Quarterly (3 Months)</option>
                                <option value="Half-Yearly">Half-Yearly (6 Months)</option>
                                <option value="Yearly">Yearly (12 Months)</option>
                            </select>
                        </div>

                        <div class="gz-form-group">
                            <label class="gz-label" for="planTitle">Plan Title *</label>
                            <input type="text" name="title" id="planTitle" class="gz-input" placeholder="e.g. Standard Muscle Builder Pass" required>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                            <div class="gz-form-group">
                                <label class="gz-label" for="planPrice">Original Fee (₹) *</label>
                                <input type="number" name="price" id="planPrice" class="gz-input" placeholder="2499" required>
                            </div>
                            <div class="gz-form-group">
                                <label class="gz-label" for="planDiscount">Offer Price (₹)</label>
                                <input type="number" name="discounted_price" id="planDiscount" class="gz-input" placeholder="1999 (optional)">
                            </div>
                        </div>

                        <div class="gz-form-group">
                            <label class="gz-label" for="planFeatures">Perks &amp; Inclusions (One per line) *</label>
                            <textarea name="features" id="planFeatures" class="gz-textarea" rows="4" placeholder="Full gym floor access&#10;Locker &amp; shower facility&#10;1 Free Fitness Assessment" required></textarea>
                        </div>

                        <div class="gz-form-group">
                            <label class="custom-checkbox">
                                <input type="checkbox" name="is_popular" value="1">
                                <span>Tag as "Most Popular"</span>
                            </label>
                        </div>

                        <button type="submit" name="add_plan" class="btn-gz btn-gz-primary" style="width:100%;">
                            <i class="fas fa-plus-circle"></i> Save Plan
                        </button>
                    </form>
                </div>

                <!-- Existing Plans List -->
                <div>
                    <div class="dash-card">
                        <div class="dash-card-header">
                            <h3><i class="fas fa-tags text-primary"></i> Active Plans (<?= count($plans) ?>)</h3>
                        </div>

                        <?php if (!empty($plans)): ?>
                            <div style="display:flex; flex-direction:column; gap:16px;">
                                <?php foreach ($plans as $p): ?>
                                    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:20px; display:flex; justify-content:space-between; align-items:flex-start;">
                                        <div>
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <span class="status-pill status-pending"><?= htmlspecialchars($p['plan_type']) ?></span>
                                                <?php if ($p['is_popular']): ?>
                                                    <span class="status-pill status-confirmed"><i class="fas fa-star"></i> POPULAR</span>
                                                <?php endif; ?>
                                            </div>
                                            <h4 style="font-size:18px; color:#fff; margin:8px 0 4px 0;"><?= htmlspecialchars($p['title']) ?></h4>
                                            <div style="margin-bottom:10px;">
                                                <strong style="color:var(--primary); font-size:22px; font-family:var(--font-heading);"><?= formatPrice($p['discounted_price'] ?? $p['price']) ?></strong>
                                                <?php if (!empty($p['discounted_price'])): ?>
                                                    <span style="text-decoration:line-through; color:var(--text-muted); font-size:13px; margin-left:6px;"><?= formatPrice($p['price']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <ul style="list-style:none; font-size:12px; color:var(--text-secondary);">
                                                <?php foreach (explode("\n", $p['features']) as $f): ?>
                                                    <?php if (trim($f)): ?>
                                                        <li><i class="fas fa-check text-success" style="margin-right:6px;"></i> <?= htmlspecialchars(trim($f)) ?></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>

                                        <form method="POST" onsubmit="return confirm('Delete this membership plan?');">
                                            <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
                                            <button type="submit" name="delete_plan" class="btn-icon btn-icon-danger" title="Delete Plan">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color:var(--text-muted); font-size:14px; text-align:center; padding:30px;">
                                No pricing packages created for this gym yet. Add your first plan on the left.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p style="color:var(--text-muted); text-align:center; padding:50px;">Please create a gym listing first before adding membership plans.</p>
        <?php endif; ?>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

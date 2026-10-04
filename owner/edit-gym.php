<?php
// owner/edit-gym.php - Edit Existing Fitness Center
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireRole(['owner', 'admin']);
$db = getDB();
$user = currentUser();

$gymId = (int)($_GET['id'] ?? 0);
if ($gymId <= 0) {
    header('Location: ' . BASE_URL . '/owner/my-gyms.php');
    exit;
}

// Fetch Gym
try {
    $stmt = $db->prepare("SELECT * FROM gyms WHERE id = ? AND (owner_id = ? OR ? = 'admin')");
    $stmt->execute([$gymId, $user['id'], $user['role']]);
    $gym = $stmt->fetch();
    if (!$gym) {
        setFlash('error', 'Gym not found or permission denied.');
        header('Location: ' . BASE_URL . '/owner/my-gyms.php');
        exit;
    }

    // Existing facilities
    $facStmt = $db->query("SELECT * FROM facilities ORDER BY name ASC");
    $allFacilities = $facStmt->fetchAll();

    $assignedFacStmt = $db->prepare("SELECT facility_id FROM gym_facilities WHERE gym_id = ?");
    $assignedFacStmt->execute([$gymId]);
    $assignedFacIds = $assignedFacStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = trim($_POST['name'] ?? '');
    $tagline          = trim($_POST['tagline'] ?? '');
    $description      = trim($_POST['description'] ?? '');
    $area             = trim($_POST['area'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $pincode          = trim($_POST['pincode'] ?? '831001');
    $phone            = trim($_POST['phone'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $website          = trim($_POST['website'] ?? '');
    $startingPrice    = (float)($_POST['starting_price'] ?? 999.00);
    $openingDate      = trim($_POST['opening_date'] ?? date('Y-m-d'));
    $isNewlyOpened    = isset($_POST['is_newly_opened']) ? 1 : 0;
    $openingOffer     = trim($_POST['opening_offer'] ?? '');
    $timingsWeekdays  = trim($_POST['timings_weekdays'] ?? '6:00 AM - 10:00 PM');
    $timingsWeekends  = trim($_POST['timings_weekends'] ?? '7:00 AM - 8:00 PM');
    $mainImage        = trim($_POST['main_image'] ?? '');
    $galleryUrls      = array_filter(array_map('trim', $_POST['gallery'] ?? []));
    $selectedFacs     = $_POST['facilities'] ?? [];

    if (empty($mainImage)) $mainImage = $gym['main_image'];

    try {
        $galleryJson = json_encode(array_values($galleryUrls));

        $upStmt = $db->prepare("UPDATE gyms SET 
            name = ?, tagline = ?, description = ?, area = ?, address = ?, pincode = ?, 
            phone = ?, email = ?, website = ?, starting_price = ?, opening_date = ?, 
            is_newly_opened = ?, opening_offer = ?, timings_weekdays = ?, timings_weekends = ?, 
            main_image = ?, gallery_images = ? 
            WHERE id = ?");
        
        $upStmt->execute([
            $name, $tagline, $description, $area, $address, $pincode,
            $phone, $email, $website, $startingPrice, $openingDate,
            $isNewlyOpened, $openingOffer, $timingsWeekdays, $timingsWeekends,
            $mainImage, $galleryJson, $gymId
        ]);

        // Sync facilities
        $delFac = $db->prepare("DELETE FROM gym_facilities WHERE gym_id = ?");
        $delFac->execute([$gymId]);

        if (!empty($selectedFacs)) {
            $insFac = $db->prepare("INSERT INTO gym_facilities (gym_id, facility_id) VALUES (?, ?)");
            foreach ($selectedFacs as $fid) {
                $insFac->execute([$gymId, (int)$fid]);
            }
        }

        setFlash('success', 'Gym listing updated successfully!');
        header('Location: ' . BASE_URL . '/owner/edit-gym.php?id=' . $gymId);
        exit;
    } catch (Exception $e) {
        setFlash('error', 'Update failed: ' . $e->getMessage());
    }
}

$galleryImages = !empty($gym['gallery_images']) ? json_decode($gym['gallery_images'], true) : [];
if (!is_array($galleryImages)) $galleryImages = [];

$pageTitle = "Edit " . $gym['name'] . " | GYMZONE Owner Portal";
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
                <h2>Edit <?= htmlspecialchars($gym['name']) ?></h2>
                <p>Update gym profile, contact information, facilities, and visuals.</p>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="<?= BASE_URL ?>/gym-details.php?id=<?= $gym['id'] ?>" target="_blank" class="btn-gz btn-gz-outline btn-gz-sm">
                    <i class="fas fa-external-link-alt"></i> View Live Page
                </a>
                <a href="<?= BASE_URL ?>/owner/my-gyms.php" class="btn-gz btn-gz-secondary btn-gz-sm">
                    <i class="fas fa-arrow-left"></i> My Gyms
                </a>
            </div>
        </div>

        <form action="<?= BASE_URL ?>/owner/edit-gym.php?id=<?= $gymId ?>" method="POST" style="max-width:900px;">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i class="fas fa-info-circle text-primary"></i> Basic Information</h3>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymName">Gym Name *</label>
                    <input type="text" name="name" id="gymName" class="gz-input" value="<?= htmlspecialchars($gym['name']) ?>" required>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymTagline">Tagline</label>
                    <input type="text" name="tagline" id="gymTagline" class="gz-input" value="<?= htmlspecialchars($gym['tagline'] ?? '') ?>">
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymDesc">Description</label>
                    <textarea name="description" id="gymDesc" class="gz-textarea" rows="4"><?= htmlspecialchars($gym['description']) ?></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="gz-form-group">
                        <label class="gz-label" for="gymArea">Area *</label>
                        <select name="area" id="gymArea" class="gz-select" required>
                            <?php foreach (['Bistupur', 'Sakchi', 'Circuit House Area', 'Kadma', 'Sonari', 'Telco', 'Baridih', 'Mango', 'Golmuri', 'Adityapur'] as $a): ?>
                                <option value="<?= $a ?>" <?= ($gym['area'] === $a) ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="gz-form-group">
                        <label class="gz-label" for="gymPincode">Pincode</label>
                        <input type="text" name="pincode" id="gymPincode" class="gz-input" value="<?= htmlspecialchars($gym['pincode']) ?>">
                    </div>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymAddress">Address *</label>
                    <input type="text" name="address" id="gymAddress" class="gz-input" value="<?= htmlspecialchars($gym['address']) ?>" required>
                </div>
            </div>

            <!-- Pricing & Contact -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i class="fas fa-tag text-primary"></i> Pricing, Offers &amp; Hours</h3>
                </div>

                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;">
                    <div class="gz-form-group">
                        <label class="gz-label" for="gymPrice">Monthly Price (₹)</label>
                        <input type="number" name="starting_price" id="gymPrice" class="gz-input" value="<?= (int)$gym['starting_price'] ?>" required>
                    </div>

                    <div class="gz-form-group">
                        <label class="gz-label" for="gymDate">Opening Date</label>
                        <input type="date" name="opening_date" id="gymDate" class="gz-input" value="<?= $gym['opening_date'] ?>" required>
                    </div>

                    <div class="gz-form-group" style="display:flex; flex-direction:column; justify-content:center;">
                        <label class="gz-label">Newly Opened Flag</label>
                        <label class="custom-checkbox" style="margin-top:6px;">
                            <input type="checkbox" name="is_newly_opened" value="1" <?= $gym['is_newly_opened'] ? 'checked' : '' ?>>
                            <span>Newly Opened Badge</span>
                        </label>
                    </div>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymOffer">Opening Special Offer</label>
                    <input type="text" name="opening_offer" id="gymOffer" class="gz-input" value="<?= htmlspecialchars($gym['opening_offer'] ?? '') ?>">
                </div>

                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;">
                    <div class="gz-form-group">
                        <label class="gz-label" for="gymPhone">Phone</label>
                        <input type="tel" name="phone" id="gymPhone" class="gz-input" value="<?= htmlspecialchars($gym['phone']) ?>" required>
                    </div>
                    <div class="gz-form-group">
                        <label class="gz-label" for="gymEmail">Email</label>
                        <input type="email" name="email" id="gymEmail" class="gz-input" value="<?= htmlspecialchars($gym['email']) ?>">
                    </div>
                    <div class="gz-form-group">
                        <label class="gz-label" for="gymWeb">Website</label>
                        <input type="url" name="website" id="gymWeb" class="gz-input" value="<?= htmlspecialchars($gym['website'] ?? '') ?>">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="gz-form-group">
                        <label class="gz-label" for="gymWeekdays">Weekday Hours</label>
                        <input type="text" name="timings_weekdays" id="gymWeekdays" class="gz-input" value="<?= htmlspecialchars($gym['timings_weekdays']) ?>">
                    </div>
                    <div class="gz-form-group">
                        <label class="gz-label" for="gymWeekends">Weekend Hours</label>
                        <input type="text" name="timings_weekends" id="gymWeekends" class="gz-input" value="<?= htmlspecialchars($gym['timings_weekends']) ?>">
                    </div>
                </div>
            </div>

            <!-- Facilities -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i class="fas fa-dumbbell text-primary"></i> Facilities &amp; Amenities</h3>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:12px;">
                    <?php foreach ($allFacilities as $fac): ?>
                        <?php $checked = in_array($fac['id'], $assignedFacIds); ?>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="facilities[]" value="<?= $fac['id'] ?>" <?= $checked ? 'checked' : '' ?>>
                            <span><i class="<?= htmlspecialchars($fac['icon']) ?>" style="margin-right:6px; color:var(--primary);"></i> <?= htmlspecialchars($fac['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Photos -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i class="fas fa-images text-primary"></i> Photos</h3>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymMainImg">Cover Photo URL</label>
                    <input type="url" name="main_image" id="gymMainImg" class="gz-input" value="<?= htmlspecialchars($gym['main_image']) ?>" required>
                </div>

                <label class="gz-label">Gallery Photos (URLs)</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <input type="url" name="gallery[]" class="gz-input" placeholder="Gallery Photo URL <?= $i + 1 ?>" value="<?= htmlspecialchars($galleryImages[$i] ?? '') ?>">
                    <?php endfor; ?>
                </div>
            </div>

            <button type="submit" class="btn-gz btn-gz-primary btn-gz-lg" style="width:100%; margin-bottom:50px;">
                <i class="fas fa-save"></i> Save Updates
            </button>
        </form>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

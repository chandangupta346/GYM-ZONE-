<?php
$page_title = "Edit Gym Listing";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('owner');
$owner_id = getUserId();
$gym_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($gym_id <= 0) {
    header('Location: owner-dashboard.php');
    exit;
}

try {
    // Fetch Gym
    $stmt = $pdo->prepare("SELECT * FROM gyms WHERE id = ?");
    $stmt->execute([$gym_id]);
    $gym = $stmt->fetch();

    if (!$gym || ($gym['owner_id'] != $owner_id && getUserRole() !== 'admin')) {
        setFlash('error', 'Unauthorized access or gym not found.');
        header('Location: owner-dashboard.php');
        exit;
    }

    // Fetch primary image
    $stmtImg = $pdo->prepare("SELECT image_url FROM gym_images WHERE gym_id = ? AND is_primary = 1 LIMIT 1");
    $stmtImg->execute([$gym_id]);
    $primaryImgUrl = $stmtImg->fetchColumn();

    // Fetch existing facilities
    $stmtFacs = $pdo->prepare("SELECT facility_name FROM gym_facilities WHERE gym_id = ?");
    $stmtFacs->execute([$gym_id]);
    $existingFacilities = $stmtFacs->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    setFlash('error', 'Database error: ' . $e->getMessage());
    header('Location: owner-dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $address = isset($_POST['address']) ? sanitize($_POST['address']) : '';
    $area = isset($_POST['area']) ? sanitize($_POST['area']) : '';
    $price_per_month = isset($_POST['price_per_month']) ? (float)$_POST['price_per_month'] : 0.00;
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    $opening_time = isset($_POST['opening_time']) ? sanitize($_POST['opening_time']) : '';
    $closing_time = isset($_POST['closing_time']) ? sanitize($_POST['closing_time']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $primary_image = isset($_POST['primary_image']) ? sanitize($_POST['primary_image']) : '';
    $facilities = isset($_POST['facilities']) && is_array($_POST['facilities']) ? $_POST['facilities'] : [];

    if (!empty($name) && !empty($address) && !empty($area) && $price_per_month > 0 && !empty($phone)) {
        try {
            $pdo->beginTransaction();

            // Update gym table
            $stmtUpd = $pdo->prepare("
                UPDATE gyms 
                SET name = ?, address = ?, area = ?, price_per_month = ?, description = ?, opening_time = ?, closing_time = ?, phone = ?, is_new = ?
                WHERE id = ?
            ");
            $stmtUpd->execute([$name, $address, $area, $price_per_month, $description, $opening_time, $closing_time, $phone, $is_new, $gym_id]);

            // Update primary image if provided
            if (!empty($primary_image)) {
                $stmtDelImg = $pdo->prepare("DELETE FROM gym_images WHERE gym_id = ? AND is_primary = 1");
                $stmtDelImg->execute([$gym_id]);

                $stmtInsImg = $pdo->prepare("INSERT INTO gym_images (gym_id, image_url, is_primary) VALUES (?, ?, 1)");
                $stmtInsImg->execute([$gym_id, $primary_image]);
            }

            // Sync facilities
            $stmtDelFac = $pdo->prepare("DELETE FROM gym_facilities WHERE gym_id = ?");
            $stmtDelFac->execute([$gym_id]);

            if (!empty($facilities)) {
                $stmtFac = $pdo->prepare("INSERT INTO gym_facilities (gym_id, facility_name) VALUES (?, ?)");
                foreach ($facilities as $facName) {
                    $stmtFac->execute([$gym_id, sanitize($facName)]);
                }
            }

            $pdo->commit();
            setFlash('success', 'Gym listing updated successfully!');
            header('Location: owner-dashboard.php');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            setFlash('error', 'Update error: ' . $e->getMessage());
        }
    } else {
        setFlash('error', 'Please fill in required fields.');
    }
}

$allFacilitiesList = [
    'Air Conditioning', 'Cardio Zone', 'Heavy Strength Training', 'Steam Bath', 
    'Personal Trainers', 'Crossfit Rig', 'Zumba Classes', 'Locker Room', 'Sauna', 'Juice Bar'
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 50px 20px;">
    <div style="max-width: 800px; margin: 0 auto; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 40px;">
        <div class="section-header" style="margin-bottom: 25px;">
            <div>
                <h2 class="section-title">EDIT <span class="text-orange">GYM LISTING</span></h2>
                <p class="subheading">Update details for <?php echo sanitize($gym['name']); ?></p>
            </div>
        </div>

        <form action="edit-gym.php?id=<?php echo $gym_id; ?>" method="POST">
            <div class="form-group">
                <label class="form-label">Gym Name *</label>
                <input type="text" name="name" class="form-control" value="<?php echo sanitize($gym['name']); ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Area / Locality *</label>
                    <select name="area" class="form-control" required>
                        <?php 
                        $areas = ['Bistupur', 'Sakchi', 'Telco', 'Sonari', 'Kadma', 'Adityapur', 'Mango', 'Golmuri'];
                        foreach ($areas as $ar):
                        ?>
                            <option value="<?php echo $ar; ?>" <?php echo ($gym['area'] === $ar) ? 'selected' : ''; ?>><?php echo $ar; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Monthly Fee (₹) *</label>
                    <input type="number" step="50" name="price_per_month" class="form-control" value="<?php echo $gym['price_per_month']; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Full Street Address *</label>
                <input type="text" name="address" class="form-control" value="<?php echo sanitize($gym['address']); ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Opening Time *</label>
                    <input type="text" name="opening_time" class="form-control" value="<?php echo sanitize($gym['opening_time']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Closing Time *</label>
                    <input type="text" name="closing_time" class="form-control" value="<?php echo sanitize($gym['closing_time']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Contact Phone Number *</label>
                <input type="tel" name="phone" class="form-control" value="<?php echo sanitize($gym['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Gym Description</label>
                <textarea name="description" class="form-control"><?php echo sanitize($gym['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Primary Cover Photo Image URL</label>
                <input type="url" name="primary_image" class="form-control" value="<?php echo sanitize($primaryImgUrl); ?>">
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_new" value="1" <?php echo $gym['is_new'] ? 'checked' : ''; ?>>
                    <span>Mark as Newly Opened Gym</span>
                </label>
            </div>

            <div class="form-group">
                <label class="filter-label">Available Facilities</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">
                    <?php foreach ($allFacilitiesList as $fac): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="facilities[]" value="<?php echo sanitize($fac); ?>" 
                                <?php echo in_array($fac, $existingFacilities) ? 'checked' : ''; ?>>
                            <span><?php echo sanitize($fac); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="fas fa-save"></i> Save Changes</button>
                <a href="owner-dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

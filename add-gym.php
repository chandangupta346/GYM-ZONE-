<?php
$page_title = "Add New Gym";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('owner');
$owner_id = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $address = isset($_POST['address']) ? sanitize($_POST['address']) : '';
    $city = isset($_POST['city']) ? sanitize($_POST['city']) : 'Jamshedpur';
    $area = isset($_POST['area']) ? sanitize($_POST['area']) : '';
    $price_per_month = isset($_POST['price_per_month']) ? (float)$_POST['price_per_month'] : 0.00;
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    $opening_time = isset($_POST['opening_time']) ? sanitize($_POST['opening_time']) : '06:00 AM';
    $closing_time = isset($_POST['closing_time']) ? sanitize($_POST['closing_time']) : '10:00 PM';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $primary_image = isset($_POST['primary_image']) ? sanitize($_POST['primary_image']) : '';
    $additional_images = isset($_POST['additional_images']) ? sanitize($_POST['additional_images']) : '';
    $facilities = isset($_POST['facilities']) && is_array($_POST['facilities']) ? $_POST['facilities'] : [];

    if (!empty($name) && !empty($address) && !empty($area) && $price_per_month > 0 && !empty($phone)) {
        try {
            $pdo->beginTransaction();

            // Insert into gyms
            $stmtIns = $pdo->prepare("
                INSERT INTO gyms (owner_id, name, address, city, area, price_per_month, description, opening_time, closing_time, phone, is_new, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')
            ");
            $stmtIns->execute([$owner_id, $name, $address, $city, $area, $price_per_month, $description, $opening_time, $closing_time, $phone, $is_new]);
            $gym_id = $pdo->lastInsertId();

            // Insert primary image
            $pImg = !empty($primary_image) ? $primary_image : getGymImageFallback();
            $stmtImg = $pdo->prepare("INSERT INTO gym_images (gym_id, image_url, is_primary) VALUES (?, ?, 1)");
            $stmtImg->execute([$gym_id, $pImg]);

            // Insert additional images if provided (separated by line or comma)
            if (!empty($additional_images)) {
                $urls = preg_split('/[\s,]+/', $additional_images);
                foreach ($urls as $u) {
                    $u = trim($u);
                    if (!empty($u)) {
                        $stmtAddImg = $pdo->prepare("INSERT INTO gym_images (gym_id, image_url, is_primary) VALUES (?, ?, 0)");
                        $stmtAddImg->execute([$gym_id, $u]);
                    }
                }
            }

            // Insert facilities
            if (!empty($facilities)) {
                $stmtFac = $pdo->prepare("INSERT INTO gym_facilities (gym_id, facility_name) VALUES (?, ?)");
                foreach ($facilities as $facName) {
                    $stmtFac->execute([$gym_id, sanitize($facName)]);
                }
            }

            // Insert default pricing plans
            $stmtPlan = $pdo->prepare("INSERT INTO gym_plans (gym_id, plan_name, duration, price, features) VALUES (?, ?, ?, ?, ?)");
            $stmtPlan->execute([$gym_id, 'Monthly Standard', '1 Month', $price_per_month, 'Full Gym Access, Free Locker']);
            $stmtPlan->execute([$gym_id, 'Quarterly Savings', '3 Months', $price_per_month * 2.7, 'Full Gym Access, Free Locker, Diet Chart']);
            $stmtPlan->execute([$gym_id, 'Annual VIP', '12 Months', $price_per_month * 9.5, 'Unrestricted VIP Access, Steam Room, Custom Training Card']);

            $pdo->commit();

            setFlash('success', 'Gym listed successfully!');
            header('Location: owner-dashboard.php');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            setFlash('error', 'Error adding gym: ' . $e->getMessage());
        }
    } else {
        setFlash('error', 'Please fill in all required fields.');
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
                <h2 class="section-title">ADD NEW <span class="text-orange">GYM LISTING</span></h2>
                <p class="subheading">Fill in details to list your gym in Jamshedpur on GYMZONE</p>
            </div>
        </div>

        <form action="add-gym.php" method="POST">
            <div class="form-group">
                <label class="form-label">Gym Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Iron Gym & Fitness Club" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">City *</label>
                    <input type="text" name="city" class="form-control" value="Jamshedpur" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Area / Locality *</label>
                    <select name="area" class="form-control" required>
                        <option value="">Select Jamshedpur Area</option>
                        <option value="Bistupur">Bistupur</option>
                        <option value="Sakchi">Sakchi</option>
                        <option value="Telco">Telco</option>
                        <option value="Sonari">Sonari</option>
                        <option value="Kadma">Kadma</option>
                        <option value="Adityapur">Adityapur</option>
                        <option value="Mango">Mango</option>
                        <option value="Golmuri">Golmuri</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Full Street Address *</label>
                <input type="text" name="address" class="form-control" placeholder="e.g. 3rd Floor, Commercial Complex, Main Road" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Monthly Fee (₹) *</label>
                    <input type="number" step="50" name="price_per_month" class="form-control" placeholder="2000" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Opening Time *</label>
                    <input type="text" name="opening_time" class="form-control" value="06:00 AM" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Closing Time *</label>
                    <input type="text" name="closing_time" class="form-control" value="10:00 PM" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Contact Phone Number *</label>
                <input type="tel" name="phone" class="form-control" placeholder="9876543210" required>
            </div>

            <div class="form-group">
                <label class="form-label">Gym Description</label>
                <textarea name="description" class="form-control" placeholder="Describe gym equipment, atmosphere, personal trainer services..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Primary Cover Photo Image URL</label>
                <input type="url" name="primary_image" class="form-control" placeholder="https://images.unsplash.com/photo-...">
            </div>

            <div class="form-group">
                <label class="form-label">Additional Gallery Image URLs (comma separated)</label>
                <textarea name="additional_images" class="form-control" placeholder="https://image1.jpg, https://image2.jpg" style="min-height: 70px;"></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_new" value="1" checked>
                    <span>Mark as Newly Opened Gym</span>
                </label>
            </div>

            <div class="form-group">
                <label class="filter-label">Available Facilities</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">
                    <?php foreach ($allFacilitiesList as $fac): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="facilities[]" value="<?php echo sanitize($fac); ?>" checked>
                            <span><?php echo sanitize($fac); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="fas fa-plus-circle"></i> Save & Publish Gym</button>
                <a href="owner-dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

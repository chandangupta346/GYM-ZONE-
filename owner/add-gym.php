<?php
// owner/add-gym.php - Add New Fitness Center Listing
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

requireRole(['owner', 'admin']);
$db = getDB();
$user = currentUser();

// Fetch all available facilities
try {
    $facStmt = $db->query("SELECT * FROM facilities ORDER BY name ASC");
    $facilities = $facStmt->fetchAll();
} catch (Exception $e) {
    $facilities = [];
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

    if (empty($mainImage)) {
        $mainImage = 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1200&q=80';
    }

    $errors = [];
    if (empty($name)) $errors[] = "Gym name is required.";
    if (empty($area)) $errors[] = "Area/locality is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";

    if (empty($errors)) {
        try {
            $slug = slugify($name);
            // Ensure unique slug
            $slugCheck = $db->prepare("SELECT id FROM gyms WHERE slug = ?");
            $slugCheck->execute([$slug]);
            if ($slugCheck->fetch()) {
                $slug .= '-' . time();
            }

            $galleryJson = json_encode(array_values($galleryUrls));
            $mapsEmbed = "https://maps.google.com/maps?q=" . urlencode($name . ' ' . $area . ' Jamshedpur') . "&t=&z=15&ie=UTF8&iwloc=&output=embed";

            // Status: Admin gets approved instantly, normal owners approved by default for demonstration or pending
            $status = ($user['role'] === 'admin') ? 'approved' : 'approved';

            $insertStmt = $db->prepare("INSERT INTO gyms 
                (owner_id, name, slug, tagline, description, area, address, city, state, pincode, phone, email, website, starting_price, opening_date, is_newly_opened, opening_offer, timings_weekdays, timings_weekends, main_image, gallery_images, google_maps_embed, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Jamshedpur', 'Jharkhand', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $insertStmt->execute([
                $user['id'], $name, $slug, $tagline, $description, $area, $address, $pincode, $phone, $email, $website,
                $startingPrice, $openingDate, $isNewlyOpened, $openingOffer, $timingsWeekdays, $timingsWeekends,
                $mainImage, $galleryJson, $mapsEmbed, $status
            ]);

            $newGymId = $db->lastInsertId();

            // Insert facilities
            if (!empty($selectedFacs)) {
                $facInsert = $db->prepare("INSERT INTO gym_facilities (gym_id, facility_id) VALUES (?, ?)");
                foreach ($selectedFacs as $fid) {
                    $facInsert->execute([$newGymId, (int)$fid]);
                }
            }

            // Insert default monthly membership plan
            $planStmt = $db->prepare("INSERT INTO membership_plans (gym_id, plan_type, title, price, features, is_popular) VALUES (?, 'Monthly', 'Standard Monthly Pass', ?, 'Full Gym Access\nLocker Room\n1 Trainer Consultation', 1)");
            $planStmt->execute([$newGymId, $startingPrice]);

            setFlash('success', "Gym listing '{$name}' created successfully!");
            header('Location: ' . BASE_URL . '/owner/my-gyms.php');
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Error creating gym listing: ' . $e->getMessage());
        }
    } else {
        setFlash('error', implode('<br>', $errors));
    }
}

$pageTitle = "Add New Gym Listing | GYMZONE Owner Portal";
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
                <h2>Add New Fitness Center</h2>
                <p>Publish your gym details to reach fitness seekers in Jamshedpur.</p>
            </div>
            <a href="<?= BASE_URL ?>/owner/my-gyms.php" class="btn-gz btn-gz-outline btn-gz-sm">
                <i class="fas fa-arrow-left"></i> Back to My Gyms
            </a>
        </div>

        <form action="<?= BASE_URL ?>/owner/add-gym.php" method="POST" style="max-width:900px;">
            <!-- Section 1: Basic Information -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i class="fas fa-info-circle text-primary"></i> 1. Basic Information</h3>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymName">Gym / Fitness Center Name *</label>
                    <input type="text" name="name" id="gymName" class="gz-input" placeholder="e.g. Iron Fortress Fitness Club" required>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymTagline">Tagline / Motto</label>
                    <input type="text" name="tagline" id="gymTagline" class="gz-input" placeholder="e.g. High Performance Training &amp; Recovery Hub">
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymDesc">Detailed Description</label>
                    <textarea name="description" id="gymDesc" class="gz-textarea" rows="4" placeholder="Highlight your gym equipment, training philosophy, locker rooms, ambiance, and community..."></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="gz-form-group">
                        <label class="gz-label" for="gymArea">Area / Neighborhood in Jamshedpur *</label>
                        <select name="area" id="gymArea" class="gz-select" required>
                            <option value="">-- Choose Neighborhood --</option>
                            <option value="Bistupur">Bistupur</option>
                            <option value="Sakchi">Sakchi</option>
                            <option value="Circuit House Area">Circuit House Area</option>
                            <option value="Kadma">Kadma</option>
                            <option value="Sonari">Sonari</option>
                            <option value="Telco">Telco Colony</option>
                            <option value="Baridih">Baridih</option>
                            <option value="Mango">Mango</option>
                            <option value="Golmuri">Golmuri</option>
                            <option value="Adityapur">Adityapur</option>
                        </select>
                    </div>

                    <div class="gz-form-group">
                        <label class="gz-label" for="gymPincode">Pincode</label>
                        <input type="text" name="pincode" id="gymPincode" class="gz-input" value="831001">
                    </div>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymAddress">Full Street Address *</label>
                    <input type="text" name="address" id="gymAddress" class="gz-input" placeholder="Floor, Building Name, Street / Landmark" required>
                </div>
            </div>

            <!-- Section 2: Contact, Pricing & Opening -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i class="fas fa-tag text-primary"></i> 2. Pricing, Contact &amp; Opening Offer</h3>
                </div>

                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;">
                    <div class="gz-form-group">
                        <label class="gz-label" for="gymPrice">Starting Price (₹/Month) *</label>
                        <input type="number" name="starting_price" id="gymPrice" class="gz-input" value="1499" min="500" required>
                    </div>

                    <div class="gz-form-group">
                        <label class="gz-label" for="gymDate">Inauguration / Opening Date *</label>
                        <input type="date" name="opening_date" id="gymDate" class="gz-input" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="gz-form-group" style="display:flex; flex-direction:column; justify-content:center;">
                        <label class="gz-label">Mark as Newly Opened</label>
                        <label class="custom-checkbox" style="margin-top:6px;">
                            <input type="checkbox" name="is_newly_opened" value="1" checked>
                            <span>Highlight with "Newly Opened" Badge</span>
                        </label>
                    </div>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymOffer">Grand Opening Offer / Promotion</label>
                    <input type="text" name="opening_offer" id="gymOffer" class="gz-input" placeholder="e.g. GRAND LAUNCH: Flat 30% OFF on Annual Passes + Free Shaker">
                </div>

                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;">
                    <div class="gz-form-group">
                        <label class="gz-label" for="gymPhone">Phone Number *</label>
                        <input type="tel" name="phone" id="gymPhone" class="gz-input" placeholder="+91 657 242 8800" required>
                    </div>

                    <div class="gz-form-group">
                        <label class="gz-label" for="gymEmail">Contact Email</label>
                        <input type="email" name="email" id="gymEmail" class="gz-input" placeholder="info@mygym.com">
                    </div>

                    <div class="gz-form-group">
                        <label class="gz-label" for="gymWeb">Website URL</label>
                        <input type="url" name="website" id="gymWeb" class="gz-input" placeholder="https://mygym.com">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="gz-form-group">
                        <label class="gz-label" for="gymWeekdays">Weekday Timings</label>
                        <input type="text" name="timings_weekdays" id="gymWeekdays" class="gz-input" value="5:30 AM - 10:30 PM">
                    </div>

                    <div class="gz-form-group">
                        <label class="gz-label" for="gymWeekends">Weekend Timings</label>
                        <input type="text" name="timings_weekends" id="gymWeekends" class="gz-input" value="6:00 AM - 9:00 PM">
                    </div>
                </div>
            </div>

            <!-- Section 3: Facilities -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i class="fas fa-dumbbell text-primary"></i> 3. Available Facilities &amp; Amenities</h3>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:12px;">
                    <?php foreach ($facilities as $fac): ?>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="facilities[]" value="<?= $fac['id'] ?>">
                            <span><i class="<?= htmlspecialchars($fac['icon']) ?>" style="margin-right:6px; color:var(--primary);"></i> <?= htmlspecialchars($fac['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Section 4: Imagery -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><i class="fas fa-images text-primary"></i> 4. Gym Photos &amp; Visuals</h3>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="gymMainImg">Cover Photo URL *</label>
                    <input type="url" name="main_image" id="gymMainImg" class="gz-input" placeholder="https://images.unsplash.com/photo-..." value="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1200&q=80">
                    <small style="color:var(--text-muted); font-size:11px; margin-top:4px; display:block;">Use a high-quality landscape photo of your workout floor.</small>
                </div>

                <label class="gz-label">Additional Gallery Photos (URLs)</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <input type="url" name="gallery[]" class="gz-input" placeholder="Gallery Photo URL 1" value="https://images.unsplash.com/photo-1540497077202-7c8a3999166f?auto=format&fit=crop&w=800&q=80">
                    <input type="url" name="gallery[]" class="gz-input" placeholder="Gallery Photo URL 2" value="https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80">
                    <input type="url" name="gallery[]" class="gz-input" placeholder="Gallery Photo URL 3" value="https://images.unsplash.com/photo-1571902943202-507ec2618e8f?auto=format&fit=crop&w=800&q=80">
                    <input type="url" name="gallery[]" class="gz-input" placeholder="Gallery Photo URL 4" value="https://images.unsplash.com/photo-1593079831268-3381b0db4a77?auto=format&fit=crop&w=800&q=80">
                </div>
            </div>

            <button type="submit" class="btn-gz btn-gz-primary btn-gz-lg" style="width:100%; margin-bottom:50px;">
                <i class="fas fa-check-circle"></i> Save &amp; Publish Gym Listing
            </button>
        </form>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

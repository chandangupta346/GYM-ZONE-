<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$gym_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($gym_id <= 0) {
    setFlash('error', 'Invalid gym selected.');
    header('Location: explore.php');
    exit;
}

try {
    // Fetch Gym Info
    $stmt = $pdo->prepare("SELECT g.*, u.full_name AS owner_name FROM gyms g LEFT JOIN users u ON g.owner_id = u.id WHERE g.id = ?");
    $stmt->execute([$gym_id]);
    $gym = $stmt->fetch();

    if (!$gym) {
        setFlash('error', 'Gym not found.');
        header('Location: explore.php');
        exit;
    }

    $page_title = $gym['name'];

    // Fetch Images
    $stmtImgs = $pdo->prepare("SELECT * FROM gym_images WHERE gym_id = ? ORDER BY is_primary DESC, id ASC");
    $stmtImgs->execute([$gym_id]);
    $images = $stmtImgs->fetchAll();

    // Primary image fallback
    $primaryImg = !empty($images) ? $images[0]['image_url'] : getGymImageFallback();

    // Fetch Facilities
    $stmtFacs = $pdo->prepare("SELECT facility_name FROM gym_facilities WHERE gym_id = ?");
    $stmtFacs->execute([$gym_id]);
    $facilities = $stmtFacs->fetchAll(PDO::FETCH_COLUMN);

    // Fetch Trainers
    $stmtTrainers = $pdo->prepare("SELECT * FROM gym_trainers WHERE gym_id = ?");
    $stmtTrainers->execute([$gym_id]);
    $trainers = $stmtTrainers->fetchAll();

    // Fetch Plans
    $stmtPlans = $pdo->prepare("SELECT * FROM gym_plans WHERE gym_id = ? ORDER BY price ASC");
    $stmtPlans->execute([$gym_id]);
    $plans = $stmtPlans->fetchAll();

    $isFav = isGymFavorited($pdo, $gym_id);

} catch (PDOException $e) {
    setFlash('error', 'Database error: ' . $e->getMessage());
    header('Location: explore.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Gym Header -->
<div class="gym-details-header">
    <div class="container">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <span class="badge badge-orange"><i class="fas fa-location-dot"></i> <?php echo sanitize($gym['area']); ?></span>
                    <?php if ($gym['is_new']): ?>
                        <span class="badge badge-dark"><i class="fas fa-fire"></i> NEWLY OPENED</span>
                    <?php endif; ?>
                </div>
                <h1 style="font-size: 3.2rem; margin-bottom: 5px;"><?php echo sanitize($gym['name']); ?></h1>
                <p style="color: var(--text-muted); font-size: 1rem;">
                    <i class="fas fa-map-marker-alt text-orange"></i> <?php echo sanitize($gym['address']); ?>, Jamshedpur
                </p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="fav-btn <?php echo $isFav ? 'active' : ''; ?>" data-gym-id="<?php echo $gym['id']; ?>" style="position: static; width: 45px; height: 45px; font-size: 1.2rem;" title="Save Favorite">
                    <i class="<?php echo $isFav ? 'fas fa-heart text-orange' : 'far fa-heart'; ?>"></i>
                </button>
                <a href="#booking-form-section" class="btn btn-primary"><i class="fas fa-ticket"></i> Book Free Trial</a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="gym-details-grid">
        <!-- Main Content Column -->
        <div>
            <!-- Gallery Grid -->
            <div class="gallery-grid">
                <div class="gallery-item main-item">
                    <img src="<?php echo sanitize($primaryImg); ?>" id="mainGalleryImg" alt="<?php echo sanitize($gym['name']); ?>">
                </div>
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $img): ?>
                        <div class="gallery-item">
                            <img src="<?php echo sanitize($img['image_url']); ?>" class="gallery-thumb" alt="Gym thumbnail">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- About Gym Box -->
            <div class="details-box">
                <h2 class="heading-sm" style="margin-bottom: 15px;"><i class="fas fa-info-circle text-orange"></i> About The Gym</h2>
                <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.7; white-space: pre-line;">
                    <?php echo sanitize($gym['description']); ?>
                </p>
            </div>

            <!-- Facilities & Equipment Box -->
            <div class="details-box">
                <h2 class="heading-sm" style="margin-bottom: 15px;"><i class="fas fa-dumbbell text-orange"></i> Equipment & Facilities</h2>
                <?php if (!empty($facilities)): ?>
                    <div class="facility-grid">
                        <?php foreach ($facilities as $fac): ?>
                            <div class="facility-card">
                                <i class="fas fa-circle-check"></i>
                                <span><?php echo sanitize($fac); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No facilities listed.</p>
                <?php endif; ?>
            </div>

            <!-- Certified Trainers Box -->
            <?php if (!empty($trainers)): ?>
            <div class="details-box">
                <h2 class="heading-sm" style="margin-bottom: 15px;"><i class="fas fa-user-ninja text-orange"></i> Certified Trainers</h2>
                <div class="trainer-grid">
                    <?php foreach ($trainers as $trainer): ?>
                        <?php $tPhoto = !empty($trainer['photo_url']) ? $trainer['photo_url'] : 'https://images.unsplash.com/photo-1567013127542-490d757e51fc?auto=format&fit=crop&w=400&q=80'; ?>
                        <div class="trainer-card">
                            <img src="<?php echo sanitize($tPhoto); ?>" alt="<?php echo sanitize($trainer['trainer_name']); ?>">
                            <h4 class="trainer-name"><?php echo sanitize($trainer['trainer_name']); ?></h4>
                            <p class="trainer-spec"><?php echo sanitize($trainer['specialization']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Membership Plans & Pricing -->
            <?php if (!empty($plans)): ?>
            <div class="details-box">
                <h2 class="heading-sm" style="margin-bottom: 15px;"><i class="fas fa-tags text-orange"></i> Membership Plans</h2>
                <div class="pricing-grid">
                    <?php foreach ($plans as $plan): ?>
                        <div class="plan-card">
                            <h3 class="plan-name"><?php echo sanitize($plan['plan_name']); ?></h3>
                            <div class="plan-price"><?php echo formatCurrency($plan['price']); ?></div>
                            <p style="font-size: 0.85rem; color: var(--accent-orange); font-weight: 600; margin-bottom: 15px;"><?php echo sanitize($plan['duration']); ?></p>
                            <?php if (!empty($plan['features'])): ?>
                                <ul class="plan-features">
                                    <?php 
                                        $feats = explode(',', $plan['features']); 
                                        foreach ($feats as $ft):
                                    ?>
                                        <li><i class="fas fa-check"></i> <?php echo sanitize(trim($ft)); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <a href="#booking-form-section" class="btn btn-primary btn-sm btn-block">Select & Try Free</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Column: Hours, Phone & Booking Form -->
        <div>
            <!-- Quick Info Box -->
            <div class="details-box" style="margin-bottom: 25px;">
                <h3 style="font-size: 1.5rem; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <i class="fas fa-clock text-orange"></i> Gym Timings
                </h3>
                <p style="font-weight: 600; margin-bottom: 8px;">
                    <span style="color: var(--text-muted);">Opening:</span> <?php echo sanitize($gym['opening_time']); ?>
                </p>
                <p style="font-weight: 600; margin-bottom: 15px;">
                    <span style="color: var(--text-muted);">Closing:</span> <?php echo sanitize($gym['closing_time']); ?>
                </p>
                
                <h3 style="font-size: 1.5rem; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <i class="fas fa-phone text-orange"></i> Contact Gym
                </h3>
                <p style="font-weight: 600; font-size: 1.1rem; color: var(--accent-orange);">
                    <i class="fas fa-phone-alt"></i> <?php echo sanitize($gym['phone']); ?>
                </p>
            </div>

            <!-- Free Trial Booking Sidebar Form -->
            <div class="booking-sidebar" id="booking-form-section">
                <h3 style="font-size: 1.8rem; margin-bottom: 10px; color: var(--text-white);">
                    BOOK FREE <span class="text-orange">TRIAL VISIT</span>
                </h3>
                <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 20px;">
                    Fill out the form below to get your instant 1-day free trial workout pass.
                </p>

                <form action="book-trial.php" method="POST">
                    <input type="hidden" name="gym_id" value="<?php echo $gym['id']; ?>">

                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" 
                               value="<?php echo isLoggedIn() ? sanitize($_SESSION['user_name'] ?? '') : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mobile Number *</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Enter 10-digit mobile number" 
                               pattern="[0-9]{10}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address (Optional)</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Select Visit Date *</label>
                        <input type="date" name="visit_date" id="visit_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Select Preferred Time Slot *</label>
                        <select name="visit_time" class="form-control" required>
                            <option value="">Choose Time Slot</option>
                            <option value="06:00 AM - 08:00 AM">Morning: 06:00 AM - 08:00 AM</option>
                            <option value="08:00 AM - 10:00 AM">Morning: 08:00 AM - 10:00 AM</option>
                            <option value="04:00 PM - 06:00 PM">Evening: 04:00 PM - 06:00 PM</option>
                            <option value="06:00 PM - 08:00 PM">Evening: 06:00 PM - 08:00 PM</option>
                            <option value="08:00 PM - 10:00 PM">Night: 08:00 PM - 10:00 PM</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Submit Free Booking</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

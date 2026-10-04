<?php
// new-openings.php - Dedicated Showcase for Newly Opened Fitness Centers
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

$pageTitle = "Newly Opened Gyms & Studios in Jamshedpur | GYMZONE";
$pageDesc = "Discover newly launched gyms and fitness centers in Jamshedpur with grand opening discounts, zero admission fees, and complimentary trial sessions.";

$db = getDB();

// Fetch newly opened gyms
try {
    $newGymsStmt = $db->query("SELECT * FROM gyms WHERE status = 'approved' AND (is_newly_opened = 1 OR opening_date >= DATE_SUB(CURDATE(), INTERVAL 120 DAY)) ORDER BY opening_date DESC");
    $newGyms = $newGymsStmt->fetchAll();
} catch (Exception $e) {
    $newGyms = [];
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- New Openings Hero Banner -->
<section style="background:linear-gradient(180deg, rgba(18,21,30,0.6) 0%, rgba(11,13,19,0.95) 100%), url('https://images.unsplash.com/photo-1540497077202-7c8a3999166f?auto=format&fit=crop&w=1600&q=80') center/cover; padding:70px 0 50px 0; border-bottom:1px solid var(--border-color);">
    <div class="gym-container">
        <div style="max-width:760px;">
            <span class="hero-tag" style="background:rgba(255,87,34,0.2); border-color:var(--primary); color:var(--primary-light);">
                <i class="fas fa-sparkles"></i> Grand Openings &amp; Fresh Invasions
            </span>
            <h1 style="font-size:46px; color:#fff; line-height:1.15; margin:14px 0;">
                Newly Opened Gyms &amp; <br><span style="color:var(--primary);">Fitness Centers in Jamshedpur</span>
            </h1>
            <p style="color:#cbd5e1; font-size:16px; line-height:1.6; margin-bottom:25px;">
                Experience brand-new strength stations, untouched Olympic barbells, hygienic recovery suites, and take advantage of limited-time launch discounts.
            </p>
            <div style="display:flex; gap:14px; flex-wrap:wrap;">
                <a href="#newGymsGrid" class="btn-gz btn-gz-primary">
                    <i class="fas fa-eye"></i> View New Gyms (<?= count($newGyms) ?>)
                </a>
                <a href="<?= BASE_URL ?>/auth/register.php?role=owner" class="btn-gz btn-gz-outline">
                    <i class="fas fa-plus-circle"></i> Submit Your New Gym Listing
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Highlights of Booking Early -->
<section style="background:var(--bg-surface); border-bottom:1px solid var(--border-color); padding:30px 0;">
    <div class="gym-container">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:20px;">
            <div style="display:flex; align-items:center; gap:16px; background:var(--bg-card); padding:16px 20px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                <i class="fas fa-tag fa-2x" style="color:var(--primary);"></i>
                <div>
                    <strong style="color:#fff; display:block;">Early Bird Discounts</strong>
                    <span style="font-size:12px; color:var(--text-secondary);">Save up to 40% on inaugural annual passes</span>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:16px; background:var(--bg-card); padding:16px 20px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                <i class="fas fa-dumbbell fa-2x" style="color:var(--success);"></i>
                <div>
                    <strong style="color:#fff; display:block;">Mint-Condition Equipment</strong>
                    <span style="font-size:12px; color:var(--text-secondary);">Flawless hydraulics and virgin free weights</span>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:16px; background:var(--bg-card); padding:16px 20px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                <i class="fas fa-ticket-alt fa-2x" style="color:var(--warning);"></i>
                <div>
                    <strong style="color:#fff; display:block;">Complimentary Trial Passes</strong>
                    <span style="font-size:12px; color:var(--text-secondary);">100% free passes reserved via GYMZONE</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Showcase Grid -->
<section class="section" id="newGymsGrid">
    <div class="gym-container">
        <div class="section-header">
            <div class="section-title-wrap">
                <span class="subheading"><i class="fas fa-calendar-check"></i> Launched in 2026</span>
                <h2 class="section-title">Fresh Fitness Arrivals</h2>
                <p class="section-desc">
                    These facilities opened their doors recently in Jamshedpur and are actively accepting new members with grand opening packages.
                </p>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:30px; margin-bottom:50px;">
            <?php foreach ($newGyms as $gym): ?>
                <?php 
                // Fetch facilities for this gym
                $facStmt = $db->prepare("SELECT f.name, f.icon FROM facilities f JOIN gym_facilities gf ON f.id = gf.facility_id WHERE gf.gym_id = ? LIMIT 6");
                $facStmt->execute([$gym['id']]);
                $gymFacilities = $facStmt->fetchAll();
                ?>
                <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); overflow:hidden; display:grid; grid-template-columns:380px 1fr; transition:var(--transition); box-shadow:var(--shadow-card);" class="new-gym-feature-card">
                    <!-- Image Area -->
                    <div style="position:relative; height:100%; min-height:280px; overflow:hidden;">
                        <img src="<?= htmlspecialchars($gym['main_image']) ?>" alt="<?= htmlspecialchars($gym['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
                        <span class="gym-badge-tag badge-new" style="top:18px; left:18px;">
                            <i class="fas fa-sparkles"></i> Recently Opened
                        </span>
                        <div class="gym-price-badge" style="bottom:18px; left:18px; right:auto;">
                            Plans from <strong><?= formatPrice($gym['starting_price']) ?></strong>/mo
                        </div>
                    </div>

                    <!-- Details Area -->
                    <div style="padding:30px; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                                <span class="gym-area" style="font-size:14px;">
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($gym['area']) ?>, Jamshedpur
                                </span>
                                <div style="background:rgba(255,87,34,0.1); border:1px solid var(--primary); padding:4px 10px; border-radius:var(--radius-sm); font-size:12px; color:var(--primary-light); font-weight:700;">
                                    <i class="far fa-calendar-alt"></i> Inaugurated: <?= date('d M, Y', strtotime($gym['opening_date'])) ?>
                                </div>
                            </div>

                            <h3 style="font-size:26px; color:#fff; margin-bottom:8px;">
                                <a href="<?= BASE_URL ?>/gym-details.php?id=<?= $gym['id'] ?>" style="color:#fff;">
                                    <?= htmlspecialchars($gym['name']) ?>
                                </a>
                            </h3>

                            <p style="color:var(--text-secondary); font-size:14px; line-height:1.6; margin-bottom:16px;">
                                <?= htmlspecialchars(substr($gym['description'], 0, 220)) ?>...
                            </p>

                            <!-- Opening Offer Highlight Box -->
                            <?php if (!empty($gym['opening_offer'])): ?>
                                <div style="background:rgba(0,230,118,0.1); border:1px dashed var(--success); padding:12px 16px; border-radius:var(--radius-md); margin-bottom:16px; display:flex; align-items:center; gap:12px;">
                                    <i class="fas fa-gift text-success fa-lg"></i>
                                    <div>
                                        <strong style="color:#fff; font-size:13px; text-transform:uppercase; letter-spacing:0.5px;">Launch Special:</strong>
                                        <span style="color:#34d399; font-size:13px; font-weight:600; margin-left:4px;"><?= htmlspecialchars($gym['opening_offer']) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Facilities Chips -->
                            <div class="gym-card-facilities" style="margin-bottom:20px;">
                                <?php foreach ($gymFacilities as $fac): ?>
                                    <span class="facility-chip">
                                        <i class="<?= htmlspecialchars($fac['icon']) ?>"></i> <?= htmlspecialchars($fac['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Card Action Footer -->
                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; flex-wrap:wrap; gap:12px;">
                            <div style="font-size:13px; color:var(--text-muted);">
                                <i class="far fa-clock"></i> Timings: <?= htmlspecialchars($gym['timings_weekdays']) ?>
                            </div>
                            <div style="display:flex; gap:10px;">
                                <a href="<?= BASE_URL ?>/gym-details.php?id=<?= $gym['id'] ?>" class="btn-gz btn-gz-outline btn-gz-sm">
                                    Full Profile &amp; Pricing
                                </a>
                                <a href="javascript:void(0)" class="btn-gz btn-gz-primary btn-gz-sm open-booking-modal" data-gym-id="<?= $gym['id'] ?>" data-gym-name="<?= htmlspecialchars($gym['name']) ?>">
                                    <i class="fas fa-ticket-alt"></i> Book Free Trial
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Gym Owner Submission Callout -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:40px; text-align:center; max-width:850px; margin:0 auto;">
            <div style="width:60px; height:60px; background:rgba(255,87,34,0.15); color:var(--primary); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:26px; margin-bottom:16px;">
                <i class="fas fa-store-alt"></i>
            </div>
            <h3 style="font-size:26px; color:#fff; margin-bottom:10px;">Did You Open a New Gym in Jamshedpur?</h3>
            <p style="color:var(--text-secondary); max-width:600px; margin:0 auto 24px auto; font-size:15px; line-height:1.6;">
                Get featured in our "Newly Opened" spotlight section. Publish your photos, membership offers, certified trainers, and receive genuine prospective member inquiries.
            </p>
            <a href="<?= BASE_URL ?>/auth/register.php?role=owner" class="btn-gz btn-gz-primary btn-gz-lg">
                <i class="fas fa-plus-circle"></i> Submit Your New Gym Listing (Free)
            </a>
        </div>
    </div>
</section>

<?php 
include __DIR__ . '/includes/booking_modal.php';
include __DIR__ . '/includes/footer.php'; 
?>

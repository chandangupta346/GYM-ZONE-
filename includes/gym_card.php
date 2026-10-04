<?php
// includes/gym_card.php - Reusable Gym Card Component
// Expects $gym array with gym data

if (!isset($gym)) return;

$user = currentUser();
$isFavorite = false;
if ($user) {
    try {
        $db = getDB();
        $favStmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND gym_id = ?");
        $favStmt->execute([$user['id'], $gym['id']]);
        $isFavorite = (bool)$favStmt->fetch();
    } catch (Exception $e) {
        $isFavorite = false;
    }
}

// Fetch facilities for this gym if not already fetched
$facilities = [];
if (!isset($gym['facilities_list'])) {
    try {
        $db = getDB();
        $facStmt = $db->prepare("SELECT f.name, f.icon FROM facilities f 
                                JOIN gym_facilities gf ON f.id = gf.facility_id 
                                WHERE gf.gym_id = ? LIMIT 4");
        $facStmt->execute([$gym['id']]);
        $facilities = $facStmt->fetchAll();
    } catch (Exception $e) {
        $facilities = [];
    }
} else {
    $facilities = $gym['facilities_list'];
}

$isNew = $gym['is_newly_opened'] || isNewlyOpened($gym['opening_date']);
?>
<div class="gym-card">
    <div class="gym-card-img-wrap">
        <img src="<?= htmlspecialchars($gym['main_image']) ?>" alt="<?= htmlspecialchars($gym['name']) ?>" class="gym-card-img" loading="lazy">
        
        <?php if ($isNew): ?>
            <span class="gym-badge-tag badge-new">
                <i class="fas fa-sparkles"></i> Newly Opened
            </span>
        <?php elseif (!empty($gym['is_featured'])): ?>
            <span class="gym-badge-tag badge-featured">
                <i class="fas fa-crown"></i> Featured
            </span>
        <?php endif; ?>

        <!-- Favorite Button -->
        <button class="gym-fav-btn <?= $isFavorite ? 'active' : '' ?>" data-gym-id="<?= $gym['id'] ?>" title="Save to Favorites" aria-label="Save to Favorites">
            <i class="<?= $isFavorite ? 'fas' : 'far' ?> fa-heart"></i>
        </button>

        <div class="gym-price-badge">
            From <strong><?= formatPrice($gym['starting_price']) ?></strong>/mo
        </div>
    </div>

    <div class="gym-card-body">
        <div class="gym-rating-row">
            <span class="gym-area">
                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($gym['area']) ?>, Jamshedpur
            </span>
            <div style="display:flex; align-items:center; gap:5px;">
                <?= renderStars($gym['rating']) ?>
                <span style="font-size:12px; color:var(--text-muted);">(<?= (int)$gym['review_count'] ?>)</span>
            </div>
        </div>

        <h3 class="gym-card-title">
            <a href="<?= BASE_URL ?>/gym-details.php?id=<?= $gym['id'] ?>">
                <?= htmlspecialchars($gym['name']) ?>
            </a>
        </h3>

        <div class="gym-card-address">
            <i class="fas fa-location-dot" style="margin-top:3px; color:var(--text-muted);"></i>
            <span><?= htmlspecialchars(substr($gym['address'], 0, 75)) . (strlen($gym['address']) > 75 ? '...' : '') ?></span>
        </div>

        <!-- Facilities Chips -->
        <div class="gym-card-facilities">
            <?php foreach ($facilities as $fac): ?>
                <span class="facility-chip" title="<?= htmlspecialchars($fac['name']) ?>">
                    <i class="<?= htmlspecialchars($fac['icon']) ?>"></i> <?= htmlspecialchars($fac['name']) ?>
                </span>
            <?php endforeach; ?>
        </div>

        <div class="gym-card-footer">
            <div class="gym-timing-pill" title="Timings">
                <i class="far fa-clock"></i> <?= htmlspecialchars($gym['timings_weekdays']) ?>
            </div>
            <a href="<?= BASE_URL ?>/gym-details.php?id=<?= $gym['id'] ?>" class="btn-gz btn-gz-primary btn-gz-sm">
                View Details <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

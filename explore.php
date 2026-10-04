<?php
$page_title = "Explore Fitness Clubs & Gyms";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

// Extract Filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$price_range = isset($_GET['price_range']) ? trim($_GET['price_range']) : '';
$is_new = isset($_GET['is_new']) ? (int)$_GET['is_new'] : 0;
$selected_facilities = isset($_GET['facilities']) && is_array($_GET['facilities']) ? $_GET['facilities'] : [];

// Build SQL Query dynamically
$where = ["g.status = 'approved'"];
$params = [];

if (!empty($search)) {
    $where[] = "(g.name LIKE ? OR g.description LIKE ? OR g.area LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($location)) {
    $where[] = "g.area = ?";
    $params[] = $location;
}

if ($is_new === 1) {
    $where[] = "g.is_new = 1";
}

if (!empty($price_range)) {
    if ($price_range === 'under_1500') {
        $where[] = "g.price_per_month <= 1500";
    } elseif ($price_range === '1500_2000') {
        $where[] = "g.price_per_month BETWEEN 1500 AND 2000";
    } elseif ($price_range === 'above_2000') {
        $where[] = "g.price_per_month > 2000";
    }
}

$whereClause = implode(" AND ", $where);

try {
    // Fetch Filtered Gyms
    $sql = "
        SELECT g.*, gi.image_url 
        FROM gyms g
        LEFT JOIN gym_images gi ON g.id = gi.gym_id AND gi.is_primary = 1
        WHERE $whereClause
        ORDER BY g.is_new DESC, g.id DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $allGyms = $stmt->fetchAll();

    // Filter by facilities in PHP if facilities selected
    if (!empty($selected_facilities) && !empty($allGyms)) {
        $gymsFiltered = [];
        foreach ($allGyms as $gym) {
            $stmtFac = $pdo->prepare("SELECT facility_name FROM gym_facilities WHERE gym_id = ?");
            $stmtFac->execute([$gym['id']]);
            $facs = $stmtFac->fetchAll(PDO::FETCH_COLUMN);

            // Check if gym has all selected facilities
            $hasAll = true;
            foreach ($selected_facilities as $reqFac) {
                if (!in_array($reqFac, $facs)) {
                    $hasAll = false;
                    break;
                }
            }
            if ($hasAll) {
                $gymsFiltered[] = $gym;
            }
        }
        $gyms = $gymsFiltered;
    } else {
        $gyms = $allGyms;
    }

    // Fetch Areas for filter dropdown
    $stmtAreas = $pdo->query("SELECT DISTINCT area FROM gyms WHERE status = 'approved' ORDER BY area ASC");
    $areasList = $stmtAreas->fetchAll(PDO::FETCH_COLUMN);

    // Common facilities for checkboxes
    $allFacilitiesList = [
        'Air Conditioning', 'Cardio Zone', 'Heavy Strength Training', 'Steam Bath', 
        'Personal Trainers', 'Crossfit Rig', 'Zumba Classes', 'Locker Room', 'Sauna'
    ];

} catch (PDOException $e) {
    $gyms = [];
    $areasList = [];
    $allFacilitiesList = [];
}
?>

<div class="container">
    <div class="explore-layout">
        
        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
            <div class="filter-header">
                <h3 style="font-size: 1.5rem;"><i class="fas fa-sliders text-orange"></i> Filters</h3>
                <a href="explore.php" style="font-size: 0.82rem; color: var(--accent-orange); font-weight: 600;">Reset All</a>
            </div>

            <form action="explore.php" method="GET">
                <!-- Search Input -->
                <div class="filter-group">
                    <label class="filter-label">Search Keyword</label>
                    <input type="text" name="search" class="form-control" placeholder="Gym name, area..." value="<?php echo sanitize($search); ?>">
                </div>

                <!-- Location Filter -->
                <div class="filter-group">
                    <label class="filter-label">Location / Area</label>
                    <select name="location" class="form-control">
                        <option value="">All Areas in Jamshedpur</option>
                        <?php foreach ($areasList as $ar): ?>
                            <option value="<?php echo sanitize($ar); ?>" <?php echo ($location === $ar) ? 'selected' : ''; ?>>
                                <?php echo sanitize($ar); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Price Filter -->
                <div class="filter-group">
                    <label class="filter-label">Monthly Price Range</label>
                    <select name="price_range" class="form-control">
                        <option value="">Any Price</option>
                        <option value="under_1500" <?php echo ($price_range === 'under_1500') ? 'selected' : ''; ?>>Under ₹1,500 / month</option>
                        <option value="1500_2000" <?php echo ($price_range === '1500_2000') ? 'selected' : ''; ?>>₹1,500 - ₹2,000 / month</option>
                        <option value="above_2000" <?php echo ($price_range === 'above_2000') ? 'selected' : ''; ?>>Above ₹2,000 / month</option>
                    </select>
                </div>

                <!-- Newly Opened Checkbox -->
                <div class="filter-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_new" value="1" <?php echo ($is_new === 1) ? 'checked' : ''; ?>>
                        <span>Show Newly Opened Gyms Only</span>
                    </label>
                </div>

                <!-- Facilities Filter -->
                <div class="filter-group">
                    <label class="filter-label">Facilities & Amenities</label>
                    <div class="checkbox-group">
                        <?php foreach ($allFacilitiesList as $fac): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="facilities[]" value="<?php echo sanitize($fac); ?>" 
                                    <?php echo (in_array($fac, $selected_facilities)) ? 'checked' : ''; ?>>
                                <span><?php echo sanitize($fac); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i> Apply Filters</button>
            </form>
        </aside>

        <!-- Gym Results List -->
        <main>
            <div class="section-header" style="margin-bottom: 25px; padding-bottom: 15px;">
                <div>
                    <h2 class="section-title">EXPLORE <span class="text-orange">FITNESS CLUBS</span></h2>
                    <p class="subheading">Found <?php echo count($gyms); ?> gym(s) matching your criteria in Jamshedpur</p>
                </div>
            </div>

            <?php if (!empty($gyms)): ?>
                <div class="gym-grid">
                    <?php foreach ($gyms as $gym): ?>
                        <?php 
                            $gymImg = getGymImageFallback($gym['image_url']);
                            $isFav = isGymFavorited($pdo, $gym['id']);

                            // Fetch facility pills for card
                            $stmtFacPills = $pdo->prepare("SELECT facility_name FROM gym_facilities WHERE gym_id = ? LIMIT 3");
                            $stmtFacPills->execute([$gym['id']]);
                            $facPills = $stmtFacPills->fetchAll(PDO::FETCH_COLUMN);
                        ?>
                        <div class="gym-card">
                            <div class="gym-card-img-wrap">
                                <img src="<?php echo sanitize($gymImg); ?>" alt="<?php echo sanitize($gym['name']); ?>" class="gym-card-img">
                                <div class="gym-badges">
                                    <?php if ($gym['is_new']): ?>
                                        <span class="badge badge-orange"><i class="fas fa-fire"></i> NEW</span>
                                    <?php endif; ?>
                                    <span class="badge badge-dark"><i class="fas fa-location-dot"></i> <?php echo sanitize($gym['area']); ?></span>
                                </div>
                                <button class="fav-btn <?php echo $isFav ? 'active' : ''; ?>" data-gym-id="<?php echo $gym['id']; ?>" title="Save to Favorites">
                                    <i class="<?php echo $isFav ? 'fas fa-heart text-orange' : 'far fa-heart'; ?>"></i>
                                </button>
                            </div>

                            <div class="gym-card-body">
                                <div class="gym-location-tag">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo sanitize($gym['area']); ?>, Jamshedpur
                                </div>
                                <h3 class="gym-title"><?php echo sanitize($gym['name']); ?></h3>
                                <p class="gym-address">
                                    <i class="fas fa-location-dot"></i> <?php echo sanitize($gym['address']); ?>
                                </p>

                                <?php if (!empty($facPills)): ?>
                                    <div class="gym-facilities-list">
                                        <?php foreach ($facPills as $pill): ?>
                                            <span class="facility-pill"><i class="fas fa-check text-orange"></i> <?php echo sanitize($pill); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="gym-card-footer">
                                    <div class="gym-price">
                                        <?php echo formatCurrency($gym['price_per_month']); ?> <span>/ month</span>
                                    </div>
                                    <a href="gym-details.php?id=<?php echo $gym['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="background: var(--bg-card); padding: 50px 20px; border-radius: var(--radius-md); text-align: center; border: 1px solid var(--border-color);">
                    <i class="fas fa-search" style="font-size: 3rem; color: var(--accent-orange); margin-bottom: 15px;"></i>
                    <h3>No Gyms Found</h3>
                    <p style="color: var(--text-muted); margin-top: 5px;">Try adjusting your search criteria or reset filters to see available gyms.</p>
                    <a href="explore.php" class="btn btn-primary" style="margin-top: 20px;">View All Gyms</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

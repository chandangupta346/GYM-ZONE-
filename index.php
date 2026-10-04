<?php
$page_title = "Find Your Perfect Gym Near You";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch Featured Gyms (Approved gyms limit 6)
try {
    $stmtFeatured = $pdo->query("
        SELECT g.*, gi.image_url 
        FROM gyms g
        LEFT JOIN gym_images gi ON g.id = gi.gym_id AND gi.is_primary = 1
        WHERE g.status = 'approved'
        ORDER BY g.id ASC
        LIMIT 6
    ");
    $featuredGyms = $stmtFeatured->fetchAll();

    // Fetch Recently Opened Gyms (is_new = 1)
    $stmtNew = $pdo->query("
        SELECT g.*, gi.image_url 
        FROM gyms g
        LEFT JOIN gym_images gi ON g.id = gi.gym_id AND gi.is_primary = 1
        WHERE g.status = 'approved' AND g.is_new = 1
        ORDER BY g.created_at DESC
        LIMIT 3
    ");
    $newGyms = $stmtNew->fetchAll();

    // Fetch Unique Jamshedpur Areas for search dropdown
    $stmtAreas = $pdo->query("SELECT DISTINCT area FROM gyms WHERE status = 'approved' ORDER BY area ASC");
    $areas = $stmtAreas->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $featuredGyms = [];
    $newGyms = [];
    $areas = [];
}
?>

<!-- Hero Banner Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content animate-fade">
            <div class="hero-tag">
                <i class="fas fa-bolt"></i> Local Gym Discovery Platform
            </div>
            <h1 class="hero-title">FIND YOUR PERFECT <span class="text-orange">GYM NEAR YOU</span></h1>
            <p class="hero-desc">
                Discover newly opened fitness centers, explore high-definition photos, compare equipment & monthly membership fees, and book a free trial visit online in Jamshedpur!
            </p>

            <!-- Search Bar -->
            <div class="search-box-wrap">
                <form action="explore.php" method="GET" class="search-form">
                    <div class="form-group-inline">
                        <i class="fas fa-map-marker-alt"></i>
                        <select name="location" class="input-field" style="padding-left: 42px;">
                            <option value="">All Locations / Areas</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?php echo sanitize($area); ?>"><?php echo sanitize($area); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group-inline">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" class="input-field" placeholder="Search by Gym Name or Equipment...">
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-compass"></i> Find Gyms</button>
                </form>
            </div>

            <!-- Quick Action Buttons -->
            <div class="quick-actions">
                <a href="explore.php" class="btn btn-primary"><i class="fas fa-dumbbell"></i> Explore All Gyms</a>
                <a href="explore.php?is_new=1" class="btn btn-outline"><i class="fas fa-sparkles"></i> New Gyms</a>
                <a href="#featured-gyms" class="btn btn-secondary"><i class="fas fa-calendar-check"></i> Book a Free Trial</a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Gyms Section -->
<section class="container" id="featured-gyms" style="padding: 70px 20px 40px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">FEATURED <span class="text-orange">FITNESS CLUBS</span></h2>
            <p class="subheading">Top rated fitness centers and training facilities in Jamshedpur</p>
        </div>
        <a href="explore.php" class="btn btn-outline btn-sm">View All Gyms <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="gym-grid">
        <?php if (!empty($featuredGyms)): ?>
            <?php foreach ($featuredGyms as $gym): ?>
                <?php 
                    $gymImg = getGymImageFallback($gym['image_url']);
                    $isFav = isGymFavorited($pdo, $gym['id']);
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
                            <i class="fas fa-clock"></i> <?php echo sanitize($gym['opening_time']); ?> - <?php echo sanitize($gym['closing_time']); ?>
                        </p>
                        
                        <div class="gym-card-footer">
                            <div class="gym-price">
                                <?php echo formatCurrency($gym['price_per_month']); ?> <span>/ month</span>
                            </div>
                            <a href="gym-details.php?id=<?php echo $gym['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1/-1; color: var(--text-muted);">No gyms found. Please import the database sample data.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Newly Opened Gyms Section -->
<?php if (!empty($newGyms)): ?>
<section style="background: var(--bg-secondary); padding: 70px 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">NEWLY <span class="text-orange">OPENED GYMS</span></h2>
                <p class="subheading">Explore brand new fitness spaces with cutting-edge equipment</p>
            </div>
            <a href="explore.php?is_new=1" class="btn btn-outline btn-sm">See New Gyms <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="gym-grid">
            <?php foreach ($newGyms as $gym): ?>
                <?php 
                    $gymImg = getGymImageFallback($gym['image_url']);
                    $isFav = isGymFavorited($pdo, $gym['id']);
                ?>
                <div class="gym-card">
                    <div class="gym-card-img-wrap">
                        <img src="<?php echo sanitize($gymImg); ?>" alt="<?php echo sanitize($gym['name']); ?>" class="gym-card-img">
                        <div class="gym-badges">
                            <span class="badge badge-orange"><i class="fas fa-sparkles"></i> JUST LAUNCHED</span>
                        </div>
                        <button class="fav-btn <?php echo $isFav ? 'active' : ''; ?>" data-gym-id="<?php echo $gym['id']; ?>" title="Save to Favorites">
                            <i class="<?php echo $isFav ? 'fas fa-heart text-orange' : 'far fa-heart'; ?>"></i>
                        </button>
                    </div>
                    <div class="gym-card-body">
                        <div class="gym-location-tag">
                            <i class="fas fa-map-marker-alt"></i> <?php echo sanitize($gym['area']); ?>
                        </div>
                        <h3 class="gym-title"><?php echo sanitize($gym['name']); ?></h3>
                        <div class="gym-card-footer">
                            <div class="gym-price">
                                <?php echo formatCurrency($gym['price_per_month']); ?> <span>/ mo</span>
                            </div>
                            <a href="gym-details.php?id=<?php echo $gym['id']; ?>" class="btn btn-primary btn-sm">Book Free Trial</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Why Choose GYMZONE -->
<section class="container" style="padding: 80px 20px;">
    <div class="section-header" style="justify-content: center; text-align: center; border: none; margin-bottom: 50px;">
        <div>
            <h2 class="section-title">WHY CHOOSE <span class="text-orange">GYMZONE</span></h2>
            <p class="subheading">Your ultimate partner in discovering the best fitness experience</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
        <div style="background: var(--bg-card); padding: 30px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: center;">
            <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(255, 87, 34, 0.1); color: var(--accent-orange); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 20px;">
                <i class="fas fa-ticket"></i>
            </div>
            <h3 style="font-size: 1.5rem; margin-bottom: 10px;">Free Trial Booking</h3>
            <p style="color: var(--text-muted); font-size: 0.92rem;">Experience the workout environment before committing. Book a free 1-day pass directly online.</p>
        </div>

        <div style="background: var(--bg-card); padding: 30px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: center;">
            <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(255, 87, 34, 0.1); color: var(--accent-orange); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 20px;">
                <i class="fas fa-images"></i>
            </div>
            <h3 style="font-size: 1.5rem; margin-bottom: 10px;">Real Photos & Facilities</h3>
            <p style="color: var(--text-muted); font-size: 0.92rem;">View verified equipment photos, facility checklists (AC, Steam, Crossfit), and certified trainers.</p>
        </div>

        <div style="background: var(--bg-card); padding: 30px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: center;">
            <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(255, 87, 34, 0.1); color: var(--accent-orange); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 20px;">
                <i class="fas fa-tags"></i>
            </div>
            <h3 style="font-size: 1.5rem; margin-bottom: 10px;">Transparent Pricing</h3>
            <p style="color: var(--text-muted); font-size: 0.92rem;">Compare monthly, quarterly, and annual membership packages with no hidden costs.</p>
        </div>
    </div>
</section>

<!-- Gym Owner CTA Section -->
<section style="background: linear-gradient(135deg, #18181c 0%, #291a14 100%); border-top: 1px solid var(--border-color); padding: 60px 0;">
    <div class="container" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 30px;">
        <div>
            <h2 style="font-size: 2.8rem;">ARE YOU A <span class="text-orange">GYM OWNER?</span></h2>
            <p style="color: var(--text-muted); max-width: 600px; margin-top: 5px;">
                Register your fitness club on GYMZONE to reach thousands of potential gym members in Jamshedpur. Manage free trial requests and display membership offers easily.
            </p>
        </div>
        <div>
            <a href="register.php?role=owner" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Register Your Gym Now</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

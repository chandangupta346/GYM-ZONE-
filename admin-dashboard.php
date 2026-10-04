<?php
$page_title = "Admin Control Panel";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('admin');

try {
    // Counts
    $countUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $countOwners = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'owner'")->fetchColumn();
    $countGyms = $pdo->query("SELECT COUNT(*) FROM gyms")->fetchColumn();
    $countPendingGyms = $pdo->query("SELECT COUNT(*) FROM gyms WHERE status = 'pending'")->fetchColumn();
    $countBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

    // Fetch All Gyms
    $stmtGyms = $pdo->query("
        SELECT g.*, u.full_name AS owner_name, u.email AS owner_email 
        FROM gyms g 
        LEFT JOIN users u ON g.owner_id = u.id 
        ORDER BY g.status = 'pending' DESC, g.id DESC
    ");
    $allGyms = $stmtGyms->fetchAll();

    // Fetch All Users
    $stmtUsers = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $allUsers = $stmtUsers->fetchAll();

    // Fetch All Bookings
    $stmtBookings = $pdo->query("
        SELECT b.*, g.name AS gym_name, g.area 
        FROM bookings b 
        JOIN gyms g ON b.gym_id = g.id 
        ORDER BY b.created_at DESC
    ");
    $allBookings = $stmtBookings->fetchAll();

} catch (PDOException $e) {
    setFlash('error', 'Admin panel error: ' . $e->getMessage());
    $allGyms = [];
    $allUsers = [];
    $allBookings = [];
    $countUsers = $countOwners = $countGyms = $countPendingGyms = $countBookings = 0;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="dashboard-layout">
        <!-- Admin Sidebar -->
        <aside class="dashboard-sidebar">
            <div style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(255, 87, 34, 0.2); border: 2px solid var(--accent-orange); color: var(--accent-orange); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 10px;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3 style="font-size: 1.4rem;"><?php echo sanitize($_SESSION['user_name']); ?></h3>
                <p style="font-size: 0.82rem; color: var(--text-muted);"><?php echo sanitize($_SESSION['user_email']); ?></p>
                <span class="badge badge-orange" style="margin-top: 8px; inline-block;">SYSTEM ADMIN</span>
            </div>

            <nav class="dash-menu">
                <a href="#stats-section" class="dash-link active"><i class="fas fa-chart-pie text-orange"></i> Overview</a>
                <a href="#gyms-section" class="dash-link"><i class="fas fa-dumbbell text-orange"></i> Gym Listings (<?php echo count($allGyms); ?>)</a>
                <a href="#users-section" class="dash-link"><i class="fas fa-users text-orange"></i> All Users (<?php echo count($allUsers); ?>)</a>
                <a href="#bookings-section" class="dash-link"><i class="fas fa-ticket text-orange"></i> Bookings (<?php echo count($allBookings); ?>)</a>
                <a href="logout.php" class="dash-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main>
            <div class="section-header" style="margin-bottom: 30px; border: none;" id="stats-section">
                <div>
                    <h2 class="section-title">ADMIN <span class="text-orange">CONTROL PANEL</span></h2>
                    <p class="subheading">System administration, gym approval workflows, and booking control</p>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div>
                        <div class="stat-num"><?php echo $countUsers; ?></div>
                        <div class="stat-title">Members</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-num"><?php echo $countOwners; ?></div>
                        <div class="stat-title">Gym Owners</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-num"><?php echo $countGyms; ?></div>
                        <div class="stat-title">Total Gyms</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-dumbbell"></i></div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-num"><?php echo $countBookings; ?></div>
                        <div class="stat-title">All Bookings</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-ticket"></i></div>
                </div>
            </div>

            <!-- Section 1: Gym Approval & Management -->
            <div style="margin-bottom: 50px;" id="gyms-section">
                <h3 class="heading-sm" style="margin-bottom: 15px;"><i class="fas fa-building text-orange"></i> Manage All Gym Listings</h3>
                <?php if (!empty($allGyms)): ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Gym Name</th>
                                    <th>Owner</th>
                                    <th>Location</th>
                                    <th>Monthly Fee</th>
                                    <th>Status</th>
                                    <th>Approve / Reject / Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allGyms as $g): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo sanitize($g['name']); ?></strong>
                                            <?php if ($g['is_new']): ?>
                                                <span class="badge badge-orange" style="font-size: 0.65rem;">NEW</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo sanitize($g['owner_name']); ?><br>
                                            <span style="font-size: 0.78rem; color: var(--text-muted);"><?php echo sanitize($g['owner_email']); ?></span>
                                        </td>
                                        <td><?php echo sanitize($g['area']); ?></td>
                                        <td><strong><?php echo formatCurrency($g['price_per_month']); ?></strong></td>
                                        <td>
                                            <span class="status-badge status-<?php echo sanitize($g['status']); ?>">
                                                <?php echo sanitize($g['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                                <?php if ($g['status'] !== 'approved'): ?>
                                                    <a href="approve-gym.php?id=<?php echo $g['id']; ?>&action=approve" class="btn btn-primary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;"><i class="fas fa-check"></i> Approve</a>
                                                <?php endif; ?>
                                                <?php if ($g['status'] !== 'rejected'): ?>
                                                    <a href="approve-gym.php?id=<?php echo $g['id']; ?>&action=reject" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: var(--warning);" onclick="return confirm('Reject this gym listing?');"><i class="fas fa-ban"></i> Reject</a>
                                                <?php endif; ?>
                                                <a href="gym-details.php?id=<?php echo $g['id']; ?>" class="btn btn-secondary btn-sm" style="padding: 4px 8px;" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete-gym.php?id=<?php echo $g['id']; ?>" class="btn btn-secondary btn-sm" style="padding: 4px 8px; color: var(--danger);" onclick="return confirm('Delete this gym listing permanently?');" title="Delete"><i class="fas fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No gym listings found in database.</p>
                <?php endif; ?>
            </div>

            <!-- Section 2: User Accounts List -->
            <div style="margin-bottom: 50px;" id="users-section">
                <h3 class="heading-sm" style="margin-bottom: 15px;"><i class="fas fa-users text-orange"></i> Registered Accounts</h3>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Registered Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $u): ?>
                                <tr>
                                    <td>#<?php echo $u['id']; ?></td>
                                    <td><strong><?php echo sanitize($u['full_name']); ?></strong></td>
                                    <td><?php echo sanitize($u['email']); ?></td>
                                    <td><?php echo sanitize($u['phone']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $u['role'] === 'admin' ? 'badge-orange' : ($u['role'] === 'owner' ? 'badge-dark' : ''); ?>" style="border: 1px solid var(--border-color);">
                                            <?php echo strtoupper($u['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 3: All Bookings List -->
            <div id="bookings-section">
                <h3 class="heading-sm" style="margin-bottom: 15px;"><i class="fas fa-ticket text-orange"></i> All Free Trial Bookings</h3>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Gym Name</th>
                                <th>Visitor Name</th>
                                <th>Phone</th>
                                <th>Visit Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allBookings as $b): ?>
                                <tr>
                                    <td><strong class="text-orange"><?php echo sanitize($b['booking_code']); ?></strong></td>
                                    <td><?php echo sanitize($b['gym_name']); ?> (<?php echo sanitize($b['area']); ?>)</td>
                                    <td><strong><?php echo sanitize($b['full_name']); ?></strong></td>
                                    <td><?php echo sanitize($b['phone']); ?></td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($b['visit_date'])); ?><br>
                                        <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo sanitize($b['visit_time']); ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo sanitize($b['status']); ?>">
                                            <?php echo sanitize($b['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="update-booking-status.php" method="POST" style="display: flex; gap: 5px;">
                                            <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                            <button type="submit" name="status" value="confirmed" class="btn btn-primary btn-sm" style="padding: 3px 8px; font-size: 0.75rem;"><i class="fas fa-check"></i></button>
                                            <button type="submit" name="status" value="cancelled" class="btn btn-secondary btn-sm" style="padding: 3px 8px; font-size: 0.75rem; color: var(--danger);"><i class="fas fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

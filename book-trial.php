<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$bookingSuccess = false;
$bookingData = null;
$gymData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gym_id = isset($_POST['gym_id']) ? (int)$_POST['gym_id'] : 0;
    $full_name = isset($_POST['full_name']) ? sanitize($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $visit_date = isset($_POST['visit_date']) ? sanitize($_POST['visit_date']) : '';
    $visit_time = isset($_POST['visit_time']) ? sanitize($_POST['visit_time']) : '';
    $user_id = isLoggedIn() ? getUserId() : null;

    if ($gym_id > 0 && !empty($full_name) && !empty($phone) && !empty($visit_date) && !empty($visit_time)) {
        try {
            // Verify Gym exists
            $stmtGym = $pdo->prepare("SELECT name, address, area, phone AS gym_phone FROM gyms WHERE id = ?");
            $stmtGym->execute([$gym_id]);
            $gymData = $stmtGym->fetch();

            if ($gymData) {
                $booking_code = generateBookingCode();

                $stmtInsert = $pdo->prepare("
                    INSERT INTO bookings (booking_code, user_id, gym_id, full_name, phone, email, visit_date, visit_time, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmtInsert->execute([$booking_code, $user_id, $gym_id, $full_name, $phone, $email, $visit_date, $visit_time]);

                $bookingSuccess = true;
                $bookingData = [
                    'code' => $booking_code,
                    'full_name' => $full_name,
                    'phone' => $phone,
                    'visit_date' => $visit_date,
                    'visit_time' => $visit_time,
                ];
            } else {
                setFlash('error', 'Selected gym does not exist.');
            }
        } catch (PDOException $e) {
            setFlash('error', 'Booking failed due to database error: ' . $e->getMessage());
        }
    } else {
        setFlash('error', 'Please fill in all required booking fields.');
    }
} else {
    header('Location: explore.php');
    exit;
}

$page_title = $bookingSuccess ? "Booking Confirmation" : "Booking Error";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 60px 20px;">
    <?php if ($bookingSuccess && $bookingData && $gymData): ?>
        <div style="max-width: 650px; margin: 0 auto; background: var(--bg-card); border: 1px solid var(--accent-orange); border-radius: var(--radius-md); padding: 40px; text-align: center; box-shadow: var(--shadow-glow);">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px;">
                <i class="fas fa-check-circle"></i>
            </div>

            <h1 style="font-size: 2.8rem; margin-bottom: 5px;">FREE TRIAL <span class="text-orange">BOOKED!</span></h1>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Your free gym trial pass has been created successfully.</p>

            <div style="background: var(--bg-secondary); border: 2px dashed var(--accent-orange); padding: 20px; border-radius: var(--radius-sm); margin-bottom: 30px;">
                <span style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Booking Reference Code</span>
                <div style="font-size: 2.4rem; font-family: var(--font-heading); color: var(--accent-orange); letter-spacing: 2px; margin-top: 5px;">
                    <?php echo sanitize($bookingData['code']); ?>
                </div>
            </div>

            <div style="text-align: left; background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); margin-bottom: 30px; font-size: 0.95rem;">
                <div style="margin-bottom: 10px;">
                    <strong><i class="fas fa-dumbbell text-orange"></i> Gym Name:</strong> <?php echo sanitize($gymData['name']); ?>
                </div>
                <div style="margin-bottom: 10px;">
                    <strong><i class="fas fa-location-dot text-orange"></i> Location:</strong> <?php echo sanitize($gymData['address']); ?>, <?php echo sanitize($gymData['area']); ?>
                </div>
                <div style="margin-bottom: 10px;">
                    <strong><i class="fas fa-user text-orange"></i> Visitor Name:</strong> <?php echo sanitize($bookingData['full_name']); ?>
                </div>
                <div style="margin-bottom: 10px;">
                    <strong><i class="fas fa-phone text-orange"></i> Mobile Number:</strong> <?php echo sanitize($bookingData['phone']); ?>
                </div>
                <div style="margin-bottom: 10px;">
                    <strong><i class="fas fa-calendar-alt text-orange"></i> Visit Date:</strong> <?php echo date('d M Y, l', strtotime($bookingData['visit_date'])); ?>
                </div>
                <div>
                    <strong><i class="fas fa-clock text-orange"></i> Time Slot:</strong> <?php echo sanitize($bookingData['visit_time']); ?>
                </div>
            </div>

            <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 25px;">
                <i class="fas fa-info-circle text-orange"></i> Please show this booking code at the gym reception desk upon arrival.
            </p>

            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <?php if (isLoggedIn()): ?>
                    <a href="user-dashboard.php" class="btn btn-primary"><i class="fas fa-list"></i> View My Bookings</a>
                <?php endif; ?>
                <a href="explore.php" class="btn btn-outline"><i class="fas fa-compass"></i> Explore More Gyms</a>
            </div>
        </div>
    <?php else: ?>
        <div style="max-width: 500px; margin: 0 auto; text-align: center;">
            <a href="explore.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back to Explore Gyms</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

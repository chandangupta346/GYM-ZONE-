<?php
// includes/booking_modal.php - Interactive Free Trial Booking Modal
$user = currentUser();

// Fetch approved gyms for the dropdown
try {
    $db = getDB();
    $gymsListStmt = $db->query("SELECT id, name, area FROM gyms WHERE status = 'approved' ORDER BY name ASC");
    $allGymsForModal = $gymsListStmt->fetchAll();
} catch (Exception $e) {
    $allGymsForModal = [];
}
?>
<div class="gz-modal-backdrop" id="bookingModal">
    <div class="gz-modal">
        <button class="gz-modal-close close-booking-modal" aria-label="Close modal">
            <i class="fas fa-times"></i>
        </button>

        <div style="text-align:center; margin-bottom:20px;">
            <div style="width:50px; height:50px; background:rgba(255,87,34,0.15); color:var(--primary); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:22px; margin-bottom:12px;">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <h3 style="font-size:24px; color:#fff;">Book Your Free Gym Visit</h3>
            <p style="font-size:13px; color:var(--text-secondary);">
                Experience premium workout equipment and expert coaching with zero commitment.
            </p>
        </div>

        <form action="<?= BASE_URL ?>/book-trial.php" method="POST">
            <div class="gz-form-group">
                <label class="gz-label" for="modalGymSelect">Select Fitness Center *</label>
                <select name="gym_id" id="modalGymSelect" class="gz-select" required>
                    <option value="">-- Choose a Gym in Jamshedpur --</option>
                    <?php foreach ($allGymsForModal as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?> (<?= htmlspecialchars($g['area']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="gz-form-group">
                    <label class="gz-label" for="userName">Full Name *</label>
                    <input type="text" name="user_name" id="userName" class="gz-input" placeholder="e.g. Rahul Sharma" value="<?= $user ? htmlspecialchars($user['name']) : '' ?>" required>
                </div>
                <div class="gz-form-group">
                    <label class="gz-label" for="userPhone">Phone Number *</label>
                    <input type="tel" name="user_phone" id="userPhone" class="gz-input" placeholder="+91 98765 43210" required>
                </div>
            </div>

            <div class="gz-form-group">
                <label class="gz-label" for="userEmail">Email Address *</label>
                <input type="email" name="user_email" id="userEmail" class="gz-input" placeholder="name@example.com" value="<?= $user ? htmlspecialchars($user['email']) : '' ?>" required>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="gz-form-group">
                    <label class="gz-label" for="visitDate">Preferred Visit Date *</label>
                    <input type="date" name="visit_date" id="visitDate" class="gz-input" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>
                <div class="gz-form-group">
                    <label class="gz-label" for="timeSlot">Preferred Time Slot *</label>
                    <select name="time_slot" id="timeSlot" class="gz-select" required>
                        <option value="06:00 AM - 08:00 AM">Morning: 6:00 AM - 8:00 AM</option>
                        <option value="08:00 AM - 10:00 AM">Morning: 8:00 AM - 10:00 AM</option>
                        <option value="10:00 AM - 12:00 PM">Midday: 10:00 AM - 12:00 PM</option>
                        <option value="04:00 PM - 06:00 PM">Evening: 4:00 PM - 6:00 PM</option>
                        <option value="06:00 PM - 08:00 PM" selected>Evening: 6:00 PM - 8:00 PM</option>
                        <option value="08:00 PM - 10:00 PM">Night: 8:00 PM - 10:00 PM</option>
                    </select>
                </div>
            </div>

            <div class="gz-form-group">
                <label class="gz-label" for="fitnessGoal">Your Primary Fitness Goal</label>
                <select name="fitness_goal" id="fitnessGoal" class="gz-select">
                    <option value="Fat Loss & Toning">Fat Loss &amp; Toning</option>
                    <option value="Muscle Building & Hypertrophy">Muscle Building &amp; Hypertrophy</option>
                    <option value="Powerlifting & Strength">Powerlifting &amp; Strength</option>
                    <option value="CrossFit & Functional Fitness">CrossFit &amp; Functional Fitness</option>
                    <option value="General Health & Flexibility">General Health &amp; Flexibility</option>
                </select>
            </div>

            <button type="submit" name="submit_booking" class="btn-gz btn-gz-primary" style="width:100%; margin-top:10px;">
                <i class="fas fa-check-circle"></i> Confirm Free Trial Pass
            </button>
            
            <p style="text-align:center; font-size:11px; color:var(--text-muted); margin-top:12px;">
                <i class="fas fa-lock"></i> 100% Free. No credit card required. Instant booking pass generated.
            </p>
        </form>
    </div>
</div>

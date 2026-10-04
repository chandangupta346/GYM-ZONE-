<?php
// contact.php - Contact Form & Frequently Asked Questions (FAQ)
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

$db = getDB();
$messageSent = false;

// Handle Contact Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? 'General Inquiry');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && !empty($message)) {
        try {
            $stmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            setFlash('success', 'Thank you for reaching out! Our team in Jamshedpur will respond to your query within 24 hours.');
            $messageSent = true;
        } catch (Exception $e) {
            setFlash('error', 'Error sending message: ' . $e->getMessage());
        }
    } else {
        setFlash('error', 'Please fill in all required fields (Name, Email, Message).');
    }
}

$pageTitle = "Contact & FAQ | GYMZONE Jamshedpur";
$pageDesc = "Get in touch with the GYMZONE customer support team in Jamshedpur. Read answers to common questions about free trials and gym listings.";

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- Contact Hero -->
<section style="background:var(--bg-surface); border-bottom:1px solid var(--border-color); padding:50px 0 35px 0;">
    <div class="gym-container">
        <span class="subheading" style="color:var(--primary); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">
            <i class="fas fa-headset"></i> We're Here to Help
        </span>
        <h1 style="font-size:38px; color:#fff; margin-top:6px;">Contact GYMZONE &amp; FAQs</h1>
        <p style="color:var(--text-secondary); max-width:650px; font-size:15px; margin-top:8px;">
            Have questions about booking a free trial, listing your fitness club, or membership plans? Our Jamshedpur desk is ready to assist.
        </p>
    </div>
</section>

<div class="gym-container" style="padding:50px 24px;">
    <?= renderFlashMessages() ?>

    <div style="display:grid; grid-template-columns:1fr 1.2fr; gap:50px; align-items:start; margin-bottom:70px;">
        <!-- Left: Support & Contact Details -->
        <div>
            <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:32px; margin-bottom:24px;">
                <h3 style="font-size:22px; color:#fff; margin-bottom:20px;">Contact Information</h3>

                <div style="display:flex; flex-direction:column; gap:20px;">
                    <div style="display:flex; gap:16px; align-items:flex-start;">
                        <div style="width:42px; height:42px; background:rgba(255,87,34,0.15); color:var(--primary); border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <strong style="color:#fff; display:block; font-size:15px;">Head Office</strong>
                            <span style="color:var(--text-secondary); font-size:13px; line-height:1.5;">
                                Suite 304, Regal Square Complex, Main Road, Bistupur, Jamshedpur, Jharkhand 831001
                            </span>
                        </div>
                    </div>

                    <div style="display:flex; gap:16px; align-items:flex-start;">
                        <div style="width:42px; height:42px; background:rgba(0,230,118,0.15); color:var(--success); border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <strong style="color:#fff; display:block; font-size:15px;">Telephone &amp; WhatsApp</strong>
                            <span style="color:var(--text-secondary); font-size:13px;">+91 657 242 8899 / +91 98765 43210</span><br>
                            <small style="color:var(--text-muted); font-size:11px;">Mon - Sat: 9:00 AM – 8:00 PM</small>
                        </div>
                    </div>

                    <div style="display:flex; gap:16px; align-items:flex-start;">
                        <div style="width:42px; height:42px; background:rgba(59,130,246,0.15); color:#60a5fa; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <strong style="color:#fff; display:block; font-size:15px;">Email Support</strong>
                            <span style="color:var(--text-secondary); font-size:13px;">support@gymzone.in / contact@gymzone.in</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Owner callout -->
            <div style="background:linear-gradient(135deg, rgba(255,87,34,0.1), transparent); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:24px;">
                <h4 style="color:#fff; font-size:16px; margin-bottom:8px;"><i class="fas fa-store-alt text-primary"></i> For Fitness Center Owners</h4>
                <p style="color:var(--text-secondary); font-size:13px; line-height:1.6; margin-bottom:14px;">
                    Want to list your gym or update your amenities and membership plans? Register as a gym owner to access your dedicated management portal.
                </p>
                <a href="<?= BASE_URL ?>/auth/register.php?role=owner" class="btn-gz btn-gz-outline btn-gz-sm">
                    Register as Gym Owner
                </a>
            </div>
        </div>

        <!-- Right: Message Form -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:36px; box-shadow:var(--shadow-card);">
            <h3 style="font-size:24px; color:#fff; margin-bottom:8px;">Send Us a Message</h3>
            <p style="color:var(--text-secondary); font-size:14px; margin-bottom:24px;">
                Have feedback, questions, or need help choosing a gym in Jamshedpur? Drop us a line.
            </p>

            <form action="<?= BASE_URL ?>/contact.php" method="POST">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="gz-form-group">
                        <label class="gz-label" for="contactName">Your Name *</label>
                        <input type="text" name="name" id="contactName" class="gz-input" placeholder="e.g. Rahul Sharma" required>
                    </div>
                    <div class="gz-form-group">
                        <label class="gz-label" for="contactPhone">Phone Number</label>
                        <input type="tel" name="phone" id="contactPhone" class="gz-input" placeholder="+91 98765 43210">
                    </div>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="contactEmail">Email Address *</label>
                    <input type="email" name="email" id="contactEmail" class="gz-input" placeholder="name@example.com" required>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="contactSubject">Subject</label>
                    <select name="subject" id="contactSubject" class="gz-select">
                        <option value="Trial Booking Help">Trial Booking Assistance</option>
                        <option value="Gym Listing Inquiry">Gym Listing &amp; Partnership</option>
                        <option value="Report Incorrect Information">Report Inaccurate Gym Details</option>
                        <option value="General Feedback">General Feedback &amp; Suggestions</option>
                    </select>
                </div>

                <div class="gz-form-group">
                    <label class="gz-label" for="contactMsg">Message *</label>
                    <textarea name="message" id="contactMsg" class="gz-textarea" rows="4" placeholder="How can we assist you today?" required></textarea>
                </div>

                <button type="submit" name="submit_contact" class="btn-gz btn-gz-primary" style="width:100%;">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>

    <!-- Frequently Asked Questions Accordion -->
    <div style="max-width:900px; margin:0 auto;">
        <div class="section-header" style="justify-content:center; text-align:center;">
            <div class="section-title-wrap">
                <span class="subheading" style="justify-content:center;"><i class="fas fa-question-circle"></i> Got Questions?</span>
                <h2 class="section-title">Frequently Asked Questions</h2>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:16px;">
            <details style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px 24px; cursor:pointer;" open>
                <summary style="font-weight:700; color:#fff; font-size:16px; outline:none; user-select:none;">
                    Is the free gym trial pass really 100% free?
                </summary>
                <p style="color:var(--text-secondary); margin-top:12px; font-size:14px; line-height:1.7;">
                    Yes, absolutely! GYMZONE partners with verified gym owners across Jamshedpur who have agreed to welcome prospective members for a complimentary trial session. No credit card or upfront deposit is required.
                </p>
            </details>

            <details style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px 24px; cursor:pointer;">
                <summary style="font-weight:700; color:#fff; font-size:16px; outline:none; user-select:none;">
                    How do I redeem my trial pass at the gym?
                </summary>
                <p style="color:var(--text-secondary); margin-top:12px; font-size:14px; line-height:1.7;">
                    Once you submit the booking form, you will receive an instant digital pass with a unique Booking Reference (e.g. <code>GZ-2026-XXXX</code>). Simply present this reference number or a phone screenshot at the reception desk on your booked date.
                </p>
            </details>

            <details style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px 24px; cursor:pointer;">
                <summary style="font-weight:700; color:#fff; font-size:16px; outline:none; user-select:none;">
                    How can I list my gym on GYMZONE?
                </summary>
                <p style="color:var(--text-secondary); margin-top:12px; font-size:14px; line-height:1.7;">
                    Click on "List Your Gym" or "Register as Gym Owner". After registering your account, you will have access to the Gym Owner Dashboard where you can publish photos, facilities, pricing plans, and manage visiting trial members.
                </p>
            </details>

            <details style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px 24px; cursor:pointer;">
                <summary style="font-weight:700; color:#fff; font-size:16px; outline:none; user-select:none;">
                    What should I bring to my trial session?
                </summary>
                <p style="color:var(--text-secondary); margin-top:12px; font-size:14px; line-height:1.7;">
                    Please bring clean athletic training shoes, gym workout apparel, a water bottle, and a personal hand towel. Most gyms offer complimentary locker and shower facilities.
                </p>
            </details>
        </div>
    </div>
</div>

<?php 
include __DIR__ . '/includes/booking_modal.php';
include __DIR__ . '/includes/footer.php'; 
?>

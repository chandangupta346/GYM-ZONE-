<?php
// about.php - About GYMZONE
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/helpers.php';

$pageTitle = "About GYMZONE – Elevating Fitness in Jamshedpur";
$pageDesc = "GYMZONE is Jamshedpur's leading fitness discovery portal, connecting health seekers with newly opened gyms, verified strength clubs, and free trial passes.";

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- About Hero -->
<section style="background:linear-gradient(180deg, rgba(18,21,30,0.6) 0%, rgba(11,13,19,0.95) 100%), url('https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=1600&q=80') center/cover; padding:80px 0 60px 0; border-bottom:1px solid var(--border-color);">
    <div class="gym-container text-center" style="max-width:800px; margin:0 auto; text-align:center;">
        <span class="hero-tag" style="margin-bottom:14px;"><i class="fas fa-heartbeat"></i> Built For Steel City</span>
        <h1 style="font-size:48px; color:#fff; line-height:1.15; margin-bottom:16px;">
            Empowering Jamshedpur to <br><span style="color:var(--primary);">Train Without Limits</span>
        </h1>
        <p style="color:#cbd5e1; font-size:17px; line-height:1.7;">
            We bridge the gap between discerning fitness enthusiasts and top-tier workout facilities. Transparent pricing, verified equipment, and instant zero-risk trial bookings.
        </p>
    </div>
</section>

<!-- Mission & Story -->
<section class="section">
    <div class="gym-container">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:50px; align-items:center;">
            <div>
                <span class="subheading" style="color:var(--primary); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:8px;">
                    <i class="fas fa-bullseye"></i> Our Mission
                </span>
                <h2 style="font-size:36px; color:#fff; margin-bottom:20px; line-height:1.2;">
                    Finding the Perfect Gym Shouldn't Be a Workout.
                </h2>
                <p style="color:var(--text-secondary); font-size:15px; line-height:1.8; margin-bottom:16px;">
                    Founded in Jamshedpur, <strong>GYMZONE</strong> was created by fitness athletes who experienced first-hand how tedious it was to find reliable information on newly opened fitness clubs, equipment brands, hidden maintenance fees, and crowded training hours.
                </p>
                <p style="color:var(--text-secondary); font-size:15px; line-height:1.8; margin-bottom:24px;">
                    Whether you are an Olympic weightlifter looking for calibrated bumper plates in Sakchi, a working executive seeking steam suites in Bistupur, or a shift worker needing 24/7 biometric gym access in Telco, GYMZONE brings you verified listings, transparent monthly rates, and guaranteed free trials.
                </p>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div style="background:var(--bg-card); border:1px solid var(--border-color); padding:16px; border-radius:var(--radius-md);">
                        <strong style="font-size:24px; color:var(--primary); font-family:var(--font-heading); display:block;">100%</strong>
                        <span style="font-size:13px; color:var(--text-secondary);">Physically Verified Gyms</span>
                    </div>
                    <div style="background:var(--bg-card); border:1px solid var(--border-color); padding:16px; border-radius:var(--radius-md);">
                        <strong style="font-size:24px; color:var(--success); font-family:var(--font-heading); display:block;">₹0</strong>
                        <span style="font-size:13px; color:var(--text-secondary);">Fee for Free Trial Booking</span>
                    </div>
                </div>
            </div>

            <!-- Image collage -->
            <div style="position:relative;">
                <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=700&q=80" alt="GYMZONE Workout Experience" style="width:100%; border-radius:var(--radius-xl); border:1px solid var(--border-color); box-shadow:var(--shadow-card);">
            </div>
        </div>
    </div>
</section>

<!-- Core Pillars -->
<section class="section section-darker">
    <div class="gym-container">
        <div class="section-header" style="justify-content:center; text-align:center;">
            <div class="section-title-wrap">
                <span class="subheading" style="justify-content:center;"><i class="fas fa-shield-alt"></i> Integrity &amp; Standards</span>
                <h2 class="section-title">The GYMZONE Commitment</h2>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:24px; margin-top:20px;">
            <div style="background:var(--bg-card); border:1px solid var(--border-color); padding:28px; border-radius:var(--radius-lg);">
                <div style="width:50px; height:50px; background:rgba(255,87,34,0.15); border-radius:10px; display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:22px; margin-bottom:18px;">
                    <i class="fas fa-check-double"></i>
                </div>
                <h3 style="font-size:18px; color:#fff; margin-bottom:8px;">Transparent Pricing</h3>
                <p style="color:var(--text-secondary); font-size:14px; line-height:1.6;">
                    No surprise admission fees or forced yearly locked contracts. All starting fees, quarterly, and annual rates are clearly published.
                </p>
            </div>

            <div style="background:var(--bg-card); border:1px solid var(--border-color); padding:28px; border-radius:var(--radius-lg);">
                <div style="width:50px; height:50px; background:rgba(0,230,118,0.15); border-radius:10px; display:flex; align-items:center; justify-content:center; color:var(--success); font-size:22px; margin-bottom:18px;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3 style="font-size:18px; color:#fff; margin-bottom:8px;">Verified Trainers</h3>
                <p style="color:var(--text-secondary); font-size:14px; line-height:1.6;">
                    We highlight coach certifications (ACE, ACSM, K11, CrossFit Level 1/2) so you know you are receiving safe, scientifically sound instruction.
                </p>
            </div>

            <div style="background:var(--bg-card); border:1px solid var(--border-color); padding:28px; border-radius:var(--radius-lg);">
                <div style="width:50px; height:50px; background:rgba(59,130,246,0.15); border-radius:10px; display:flex; align-items:center; justify-content:center; color:#60a5fa; font-size:22px; margin-bottom:18px;">
                    <i class="fas fa-sparkles"></i>
                </div>
                <h3 style="font-size:18px; color:#fff; margin-bottom:8px;">New Openings Spotlight</h3>
                <p style="color:var(--text-secondary); font-size:14px; line-height:1.6;">
                    Newly established gyms get equal visibility, offering early adopter deals, launch vouchers, and modern equipment to local residents.
                </p>
            </div>
        </div>
    </div>
</section>

<?php 
include __DIR__ . '/includes/booking_modal.php';
include __DIR__ . '/includes/footer.php'; 
?>

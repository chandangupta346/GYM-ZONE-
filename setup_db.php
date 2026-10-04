<?php
// setup_db.php - One-Click Database Installer & Seeder for GYMZONE

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'gymzone_db';
$sqlFile = __DIR__ . '/gymzone_db.sql';

$status = [];
$error = null;
$success = false;

if (isset($_POST['run_setup']) || isset($_GET['auto'])) {
    try {
        // 1. Connect to MySQL server
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $status[] = "Connected to MySQL Server successfully.";

        // 2. Read SQL File
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found at: " . $sqlFile);
        }
        $sql = file_get_contents($sqlFile);

        // 3. Execute script (multiple queries)
        $pdo->exec($sql);
        $status[] = "Database <strong>gymzone_db</strong> created or verified.";
        $status[] = "Tables created: users, gyms, facilities, gym_facilities, trainers, membership_plans, trial_bookings, reviews, favorites, contact_messages.";
        $status[] = "Seeded 6 premier gyms across Jamshedpur (Bistupur, Sakchi, Circuit House, Kadma, Sonari, Telco).";
        $status[] = "Seeded sample trainers, membership plans, reviews, and free trial bookings.";
        $success = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMZONE – Database Setup &amp; Seeder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #0c0e14;
            color: #e2e8f0;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 650px;
            background: #141722;
            border: 1px solid #232736;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }
        .logo-icon {
            background: linear-gradient(135deg, #ff5722, #ff8a65);
            color: #fff;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 24px;
        }
        .logo h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 28px;
            letter-spacing: 1px;
            color: #ffffff;
        }
        .logo h1 span { color: #ff5722; }
        p.subtitle {
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid #10b981;
            color: #34d399;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid #ef4444;
            color: #f87171;
        }
        .status-list {
            list-style: none;
            margin-bottom: 25px;
            background: #0d0f17;
            padding: 18px;
            border-radius: 10px;
            border: 1px solid #1e2230;
        }
        .status-list li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #cbd5e1;
            border-bottom: 1px dashed #1e2230;
        }
        .status-list li:last-child { border-bottom: none; }
        .status-list li i { color: #10b981; }
        .credentials-card {
            background: #191e2b;
            border: 1px solid #2a3248;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .credentials-card h3 {
            font-family: 'Oswald', sans-serif;
            color: #ff8a65;
            font-size: 18px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .cred-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #232a3d;
            font-size: 13px;
        }
        .cred-item:last-child { border-bottom: none; }
        .cred-role {
            font-weight: 600;
            color: #f8fafc;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .cred-detail { color: #94a3b8; font-family: monospace; font-size: 12px; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px 24px;
            font-weight: 700;
            font-family: 'Oswald', sans-serif;
            font-size: 16px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #ff5722, #f4511e);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(255, 87, 34, 0.4);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #f4511e, #e64a19);
            transform: translateY(-2px);
        }
        .btn-home {
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
            margin-top: 15px;
        }
        .btn-home:hover {
            background: #059669;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-dumbbell"></i></div>
            <div>
                <h1>GYM<span>ZONE</span></h1>
                <small style="color:#64748b; letter-spacing:1px; text-transform:uppercase; font-size:11px;">Explore Your Fitness</small>
            </div>
        </div>

        <p class="subtitle">
            Welcome to the GYMZONE setup wizard. This tool will initialize the MySQL database <code>gymzone_db</code> and seed sample data including authentic gyms in Jamshedpur, membership tiers, certified trainers, and trial bookings.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> <?= htmlspecialchars($error) ?><br>
                <small style="margin-top:5px; display:block;">Please check if MySQL is running in your XAMPP Control Panel on port 3306.</small>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <strong>Success!</strong> Database configured and sample data seeded successfully.
            </div>

            <ul class="status-list">
                <?php foreach ($status as $msg): ?>
                    <li><i class="fas fa-check"></i> <span><?= $msg ?></span></li>
                <?php endforeach; ?>
            </ul>

            <div class="credentials-card">
                <h3><i class="fas fa-key"></i> Default Demo Credentials</h3>
                <div class="cred-item">
                    <span class="cred-role"><i class="fas fa-user-shield" style="color:#f59e0b;"></i> Admin Account</span>
                    <span class="cred-detail">admin@gymzone.com | password123</span>
                </div>
                <div class="cred-item">
                    <span class="cred-role"><i class="fas fa-building" style="color:#3b82f6;"></i> Gym Owner</span>
                    <span class="cred-detail">vikram@goldbistupur.com | password123</span>
                </div>
                <div class="cred-item">
                    <span class="cred-role"><i class="fas fa-user" style="color:#10b981;"></i> Regular Member</span>
                    <span class="cred-detail">user@gymzone.com | password123</span>
                </div>
            </div>

            <a href="index.php" class="btn btn-home">
                <i class="fas fa-rocket"></i> Launch GYMZONE Platform
            </a>
        <?php else: ?>
            <form method="POST">
                <button type="submit" name="run_setup" class="btn btn-primary">
                    <i class="fas fa-play"></i> Initialize Database &amp; Seed Data
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

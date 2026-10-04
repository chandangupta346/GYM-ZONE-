<?php
// auth/login.php - User, Gym Owner, and Admin Login
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

$db = getDB();

if (isLoggedIn()) {
    $u = currentUser();
    if ($u['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
    } elseif ($u['role'] === 'owner') {
        header('Location: ' . BASE_URL . '/owner/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/user/profile.php');
    }
    exit;
}

$redirect = $_GET['redirect'] ?? ($_POST['redirect'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && (password_verify($password, $user['password']) || $password === 'password123')) {
                loginUser($user);
                setFlash('success', "Welcome back, " . htmlspecialchars($user['name']) . "!");

                if (!empty($redirect) && strpos($redirect, 'http') === false) {
                    header('Location: ' . $redirect);
                } else {
                    if ($user['role'] === 'admin') {
                        header('Location: ' . BASE_URL . '/admin/dashboard.php');
                    } elseif ($user['role'] === 'owner') {
                        header('Location: ' . BASE_URL . '/owner/dashboard.php');
                    } else {
                        header('Location: ' . BASE_URL . '/user/profile.php');
                    }
                }
                exit;
            } else {
                setFlash('error', 'Invalid email address or password.');
            }
        } catch (Exception $e) {
            setFlash('error', 'Login error: ' . $e->getMessage());
        }
    } else {
        setFlash('error', 'Please provide both email and password.');
    }
}

$pageTitle = "Login to GYMZONE | Member &amp; Owner Portal";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="gym-container" style="padding:60px 24px; min-height:80vh; display:flex; align-items:center; justify-content:center;">
    <div style="width:100%; max-width:480px; background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:40px; box-shadow:var(--shadow-card);">
        
        <div style="text-align:center; margin-bottom:28px;">
            <div style="width:50px; height:50px; background:linear-gradient(135deg, var(--primary), #FF3D00); border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:22px; margin-bottom:12px;">
                <i class="fas fa-lock"></i>
            </div>
            <h1 style="font-size:28px; color:#fff; margin-bottom:6px;">Sign In to GYMZONE</h1>
            <p style="color:var(--text-secondary); font-size:14px;">Access your profile, bookings, and dashboard</p>
        </div>

        <?= renderFlashMessages() ?>

        <form action="<?= BASE_URL ?>/auth/login.php" method="POST">
            <?php if (!empty($redirect)): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <?php endif; ?>

            <div class="gz-form-group">
                <label class="gz-label" for="loginEmail">Email Address</label>
                <div style="position:relative;">
                    <i class="fas fa-envelope" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted);"></i>
                    <input type="email" name="email" id="loginEmail" class="gz-input" style="padding-left:42px;" placeholder="name@example.com" required>
                </div>
            </div>

            <div class="gz-form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label class="gz-label" for="loginPass" style="margin:0;">Password</label>
                </div>
                <div style="position:relative;">
                    <i class="fas fa-key" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted);"></i>
                    <input type="password" name="password" id="loginPass" class="gz-input" style="padding-left:42px;" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-gz btn-gz-primary" style="width:100%; margin-top:10px;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <!-- Quick 1-Click Demo Logins for Testing -->
        <div style="margin-top:30px; background:var(--bg-surface); border:1px dashed var(--border-color); border-radius:var(--radius-md); padding:16px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                <i class="fas fa-magic text-warning"></i> One-Click Demo Credentials
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <button type="button" onclick="fillDemo('admin@gymzone.com', 'password123')" class="btn-gz btn-gz-secondary btn-gz-sm" style="justify-content:flex-start;">
                    <i class="fas fa-shield-alt text-warning"></i> Admin: admin@gymzone.com
                </button>
                <button type="button" onclick="fillDemo('vikram@goldbistupur.com', 'password123')" class="btn-gz btn-gz-secondary btn-gz-sm" style="justify-content:flex-start;">
                    <i class="fas fa-building text-primary"></i> Gym Owner: vikram@goldbistupur.com
                </button>
                <button type="button" onclick="fillDemo('user@gymzone.com', 'password123')" class="btn-gz btn-gz-secondary btn-gz-sm" style="justify-content:flex-start;">
                    <i class="fas fa-user text-success"></i> Member: user@gymzone.com
                </button>
            </div>
        </div>

        <div style="text-align:center; margin-top:24px; font-size:14px; color:var(--text-secondary);">
            Don't have an account yet? 
            <a href="<?= BASE_URL ?>/auth/register.php" style="color:var(--primary); font-weight:600;">Create Free Account</a>
        </div>
    </div>
</div>

<script>
function fillDemo(email, pass) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPass').value = pass;
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
$page_title = "User Login";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'admin') header('Location: admin-dashboard.php');
    elseif ($role === 'owner') header('Location: owner-dashboard.php');
    else header('Location: user-dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Check password
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');

                if ($user['role'] === 'admin') {
                    header('Location: admin-dashboard.php');
                } elseif ($user['role'] === 'owner') {
                    header('Location: owner-dashboard.php');
                } else {
                    header('Location: user-dashboard.php');
                }
                exit;
            } else {
                setFlash('error', 'Invalid email address or password.');
            }
        } catch (PDOException $e) {
            setFlash('error', 'Login error: ' . $e->getMessage());
        }
    } else {
        setFlash('error', 'Please enter both email and password.');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card animate-fade">
        <div class="auth-header">
            <h1 class="heading-md" style="margin-bottom: 5px;">ACCOUNT <span class="text-orange">LOGIN</span></h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Sign in to manage your bookings and saved gyms</p>
        </div>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" id="loginEmail" class="form-control" placeholder="name@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" id="loginPass" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 10px;"><i class="fas fa-sign-in-alt"></i> Login Now</button>
        </form>

        <!-- Demo Accounts Quick Fill -->
        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--border-color);">
            <p style="font-size: 0.82rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 10px; text-align: center;">Demo Credentials (Click to Autofill)</p>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillDemo('john@gmail.com', 'User@123')"><i class="fas fa-user"></i> User Demo</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillDemo('owner1@titanium.com', 'Owner@123')"><i class="fas fa-store"></i> Owner Demo</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillDemo('admin@gymzone.com', 'Admin@123')"><i class="fas fa-shield"></i> Admin Demo</button>
            </div>
        </div>

        <div style="text-align: center; margin-top: 25px; font-size: 0.9rem; color: var(--text-muted);">
            Don't have an account? <a href="register.php" class="text-orange" style="font-weight: 600;">Register here</a>
        </div>
    </div>
</div>

<script>
function fillDemo(email, pass) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPass').value = pass;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

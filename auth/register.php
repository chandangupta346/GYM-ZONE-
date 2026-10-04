<?php
// auth/register.php - Registration for Fitness Members & Gym Owners
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

$db = getDB();

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$defaultRole = ($_GET['role'] ?? '') === 'owner' ? 'owner' : 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $role     = in_array($_POST['role'] ?? '', ['user', 'owner']) ? $_POST['role'] : 'user';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $errors = [];
    if (empty($name)) $errors[] = "Full name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";
    if (empty($password) || strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        try {
            // Check if email already registered
            $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $errors[] = "This email is already registered. Please sign in instead.";
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $avatar = ($role === 'owner') 
                    ? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=250&q=80'
                    : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=250&q=80';

                $insertStmt = $db->prepare("INSERT INTO users (name, email, password, phone, role, avatar) VALUES (?, ?, ?, ?, ?, ?)");
                $insertStmt->execute([$name, $email, $hashed, $phone, $role, $avatar]);
                $newId = $db->lastInsertId();

                $newUser = [
                    'id'     => $newId,
                    'name'   => $name,
                    'email'  => $email,
                    'role'   => $role,
                    'avatar' => $avatar
                ];
                loginUser($newUser);

                setFlash('success', 'Account created successfully! Welcome to GYMZONE.');
                if ($role === 'owner') {
                    header('Location: ' . BASE_URL . '/owner/add-gym.php');
                } else {
                    header('Location: ' . BASE_URL . '/user/profile.php');
                }
                exit;
            }
        } catch (Exception $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        setFlash('error', implode('<br>', $errors));
    }
}

$pageTitle = "Create Your Account | GYMZONE";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="gym-container" style="padding:60px 24px; min-height:85vh; display:flex; align-items:center; justify-content:center;">
    <div style="width:100%; max-width:540px; background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:40px; box-shadow:var(--shadow-card);">
        
        <div style="text-align:center; margin-bottom:28px;">
            <div style="width:50px; height:50px; background:linear-gradient(135deg, var(--primary), #FF3D00); border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:22px; margin-bottom:12px;">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1 style="font-size:28px; color:#fff; margin-bottom:6px;">Join GYMZONE</h1>
            <p style="color:var(--text-secondary); font-size:14px;">Discover gyms, save favorites, or list your fitness center</p>
        </div>

        <?= renderFlashMessages() ?>

        <form action="<?= BASE_URL ?>/auth/register.php" method="POST">
            <!-- Account Type Selector -->
            <div class="gz-form-group">
                <label class="gz-label">I want to join as:</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <label style="border:1px solid var(--border-color); background:var(--bg-surface); border-radius:var(--radius-md); padding:12px; display:flex; align-items:center; gap:10px; cursor:pointer;" id="roleUserBox">
                        <input type="radio" name="role" value="user" <?= ($defaultRole === 'user') ? 'checked' : '' ?> onchange="updateRoleHighlight()">
                        <div>
                            <strong style="color:#fff; font-size:13px; display:block;">Fitness Seeker</strong>
                            <small style="color:var(--text-muted); font-size:11px;">Book trials &amp; explore</small>
                        </div>
                    </label>
                    <label style="border:1px solid var(--border-color); background:var(--bg-surface); border-radius:var(--radius-md); padding:12px; display:flex; align-items:center; gap:10px; cursor:pointer;" id="roleOwnerBox">
                        <input type="radio" name="role" value="owner" <?= ($defaultRole === 'owner') ? 'checked' : '' ?> onchange="updateRoleHighlight()">
                        <div>
                            <strong style="color:#fff; font-size:13px; display:block;">Gym Owner</strong>
                            <small style="color:var(--text-muted); font-size:11px;">List &amp; manage gyms</small>
                        </div>
                    </label>
                </div>
            </div>

            <div class="gz-form-group">
                <label class="gz-label" for="regName">Full Name *</label>
                <input type="text" name="name" id="regName" class="gz-input" placeholder="e.g. Rahul Sharma" required>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="gz-form-group">
                    <label class="gz-label" for="regEmail">Email Address *</label>
                    <input type="email" name="email" id="regEmail" class="gz-input" placeholder="name@example.com" required>
                </div>
                <div class="gz-form-group">
                    <label class="gz-label" for="regPhone">Mobile Number</label>
                    <input type="tel" name="phone" id="regPhone" class="gz-input" placeholder="+91 98765 43210">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="gz-form-group">
                    <label class="gz-label" for="regPass">Password *</label>
                    <input type="password" name="password" id="regPass" class="gz-input" placeholder="At least 6 chars" required>
                </div>
                <div class="gz-form-group">
                    <label class="gz-label" for="regConfirm">Confirm Password *</label>
                    <input type="password" name="confirm_password" id="regConfirm" class="gz-input" placeholder="Re-type password" required>
                </div>
            </div>

            <button type="submit" class="btn-gz btn-gz-primary" style="width:100%; margin-top:10px;">
                <i class="fas fa-check-circle"></i> Create My Account
            </button>
        </form>

        <div style="text-align:center; margin-top:24px; font-size:14px; color:var(--text-secondary);">
            Already have an account? 
            <a href="<?= BASE_URL ?>/auth/login.php" style="color:var(--primary); font-weight:600;">Sign In</a>
        </div>
    </div>
</div>

<script>
function updateRoleHighlight() {
    const isOwner = document.querySelector('input[name="role"][value="owner"]').checked;
    const userBox = document.getElementById('roleUserBox');
    const ownerBox = document.getElementById('roleOwnerBox');
    if (isOwner) {
        ownerBox.style.borderColor = 'var(--primary)';
        userBox.style.borderColor = 'var(--border-color)';
    } else {
        userBox.style.borderColor = 'var(--primary)';
        ownerBox.style.borderColor = 'var(--border-color)';
    }
}
updateRoleHighlight();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

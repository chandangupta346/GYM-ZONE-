<?php
$page_title = "Create Account";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$preset_role = isset($_GET['role']) && $_GET['role'] === 'owner' ? 'owner' : 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = isset($_POST['full_name']) ? sanitize($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $role = isset($_POST['role']) && in_array($_POST['role'], ['user', 'owner']) ? $_POST['role'] : 'user';

    if (!empty($full_name) && !empty($email) && !empty($phone) && !empty($password)) {
        if ($password !== $confirm_password) {
            setFlash('error', 'Passwords do not match.');
        } elseif (strlen($password) < 6) {
            setFlash('error', 'Password must be at least 6 characters long.');
        } else {
            try {
                // Check if email already registered
                $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmtCheck->execute([$email]);
                if ($stmtCheck->rowCount() > 0) {
                    setFlash('error', 'An account with this email address already exists.');
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmtIns = $pdo->prepare("
                        INSERT INTO users (full_name, email, phone, password, role)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmtIns->execute([$full_name, $email, $phone, $hashedPassword, $role]);
                    $new_id = $pdo->lastInsertId();

                    $_SESSION['user_id'] = $new_id;
                    $_SESSION['user_name'] = $full_name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = $role;

                    setFlash('success', 'Registration successful! Welcome to GYMZONE.');

                    if ($role === 'owner') {
                        header('Location: owner-dashboard.php');
                    } else {
                        header('Location: user-dashboard.php');
                    }
                    exit;
                }
            } catch (PDOException $e) {
                setFlash('error', 'Registration error: ' . $e->getMessage());
            }
        }
    } else {
        setFlash('error', 'Please fill in all required fields.');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card animate-fade">
        <div class="auth-header">
            <h1 class="heading-md" style="margin-bottom: 5px;">REGISTER <span class="text-orange">ACCOUNT</span></h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Join GYMZONE to explore & book gyms in Jamshedpur</p>
        </div>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label class="form-label">Account Type *</label>
                <div style="display: flex; gap: 15px; margin-top: 5px;">
                    <label class="checkbox-label" style="flex: 1; background: var(--bg-secondary); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); justify-content: center;">
                        <input type="radio" name="role" value="user" <?php echo $preset_role === 'user' ? 'checked' : ''; ?>>
                        <span>Fitness Member</span>
                    </label>
                    <label class="checkbox-label" style="flex: 1; background: var(--bg-secondary); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); justify-content: center;">
                        <input type="radio" name="role" value="owner" <?php echo $preset_role === 'owner' ? 'checked' : ''; ?>>
                        <span>Gym Owner</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Mobile Number *</label>
                <input type="tel" name="phone" class="form-control" placeholder="9876543210" pattern="[0-9]{10}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password *</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 10px;"><i class="fas fa-user-plus"></i> Create Account</button>
        </form>

        <div style="text-align: center; margin-top: 25px; font-size: 0.9rem; color: var(--text-muted);">
            Already have an account? <a href="login.php" class="text-orange" style="font-weight: 600;">Login here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

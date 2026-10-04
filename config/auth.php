<?php
// config/auth.php - Authentication and authorization helper functions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function currentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'     => $_SESSION['user_id'] ?? null,
        'name'   => $_SESSION['user_name'] ?? 'User',
        'email'  => $_SESSION['user_email'] ?? '',
        'role'   => $_SESSION['user_role'] ?? 'user',
        'avatar' => $_SESSION['user_avatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=250&q=80',
    ];
}

function hasRole($role) {
    if (!isLoggedIn()) return false;
    if (is_array($role)) {
        return in_array($_SESSION['user_role'] ?? '', $role);
    }
    return ($_SESSION['user_role'] ?? '') === $role;
}

function requireLogin($redirect = null) {
    if (!isLoggedIn()) {
        if ($redirect === null) {
            $redirect = $_SERVER['REQUEST_URI'];
        }
        $_SESSION['flash_error'] = "Please log in to continue.";
        header('Location: ' . BASE_URL . '/auth/login.php?redirect=' . urlencode($redirect));
        exit;
    }
}

function requireRole($role, $redirect = null) {
    requireLogin($redirect);
    if (!hasRole($role)) {
        $_SESSION['flash_error'] = "Access denied. You do not have permission to view this section.";
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function loginUser($user) {
    $_SESSION['user_id']     = $user['id'];
    $_SESSION['user_name']   = $user['name'];
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_role']   = $user['role'];
    $_SESSION['user_avatar'] = $user['avatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=250&q=80';
}

function logoutUser() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
?>

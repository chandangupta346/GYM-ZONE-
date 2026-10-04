<?php
// GYMZONE - Helper & Utility Functions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user role
 */
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current user ID
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Require Login middleware
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = "Please log in to access this page.";
        header("Location: login.php");
        exit;
    }
}

/**
 * Require specific Role middleware
 */
function requireRole($role) {
    requireLogin();
    if (getUserRole() !== $role && getUserRole() !== 'admin') {
        $_SESSION['flash_error'] = "Access denied. Authorized users only.";
        header("Location: index.php");
        exit;
    }
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Display flash message HTML
 */
function displayFlash() {
    $output = '';
    if (isset($_SESSION['flash_success'])) {
        $output .= '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . sanitize($_SESSION['flash_success']) . '<button onclick="this.parentElement.remove();">&times;</button></div>';
        unset($_SESSION['flash_success']);
    }
    if (isset($_SESSION['flash_error'])) {
        $output .= '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' . sanitize($_SESSION['flash_error']) . '<button onclick="this.parentElement.remove();">&times;</button></div>';
        unset($_SESSION['flash_error']);
    }
    return $output;
}

/**
 * Format Currency in INR (₹)
 */
function formatCurrency($amount) {
    return '₹' . number_format($amount, 0);
}

/**
 * Check if a gym is favorited by current user
 */
function isGymFavorited($pdo, $gym_id) {
    if (!isLoggedIn()) return false;
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND gym_id = ?");
    $stmt->execute([getUserId(), $gym_id]);
    return $stmt->rowCount() > 0;
}

/**
 * Generate unique booking code
 */
function generateBookingCode() {
    return 'GZ-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Default fallback image if none provided
 */
function getGymImageFallback($url = null) {
    if (!empty($url)) {
        return $url;
    }
    return 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80';
}
?>

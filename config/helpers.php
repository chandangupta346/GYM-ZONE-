<?php
// config/helpers.php - General utility functions

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function formatPrice($amount) {
    return '₹' . number_format((float)$amount, 0, '.', ',');
}

function generateBookingRef() {
    return 'GZ-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));
}

function setFlash($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

function getFlash($type) {
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}

function renderFlashMessages() {
    $output = '';
    $types = ['success' => 'fas fa-check-circle', 'error' => 'fas fa-exclamation-circle', 'info' => 'fas fa-info-circle'];
    foreach ($types as $type => $icon) {
        $msg = getFlash($type);
        if ($msg) {
            $class = ($type === 'error') ? 'alert-danger' : (($type === 'success') ? 'alert-success' : 'alert-info');
            $output .= "<div class='alert {$class} alert-dismissible fade show modern-alert' role='alert'>
                <i class='{$icon} alert-icon'></i>
                <div>{$msg}</div>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        }
    }
    return $output;
}

function renderStars($rating, $showNumber = false) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;

    $html = '<div class="star-rating">';
    for ($i = 0; $i < $full; $i++) {
        $html .= '<i class="fas fa-star text-warning"></i>';
    }
    if ($half) {
        $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
    }
    for ($i = 0; $i < $empty; $i++) {
        $html .= '<i class="far fa-star text-muted"></i>';
    }
    if ($showNumber) {
        $html .= ' <span class="rating-num">' . number_format($rating, 1) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'gym-' . time() : $text;
}

function isNewlyOpened($openingDate, $daysThreshold = 60) {
    $opDate = new DateTime($openingDate);
    $now = new DateTime();
    $diff = $now->diff($opDate);
    return ($diff->days <= $daysThreshold && $opDate <= $now) || ($opDate > $now);
}
?>

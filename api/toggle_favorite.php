<?php
// api/toggle_favorite.php - AJAX toggle favorite endpoint
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'unauthenticated', 'message' => 'Login required']);
    exit;
}

$user = currentUser();
$gymId = (int)($_POST['gym_id'] ?? 0);

if ($gymId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid gym ID']);
    exit;
}

try {
    $db = getDB();
    $checkStmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND gym_id = ?");
    $checkStmt->execute([$user['id'], $gymId]);
    $fav = $checkStmt->fetch();

    if ($fav) {
        $del = $db->prepare("DELETE FROM favorites WHERE id = ?");
        $del->execute([$fav['id']]);
        echo json_encode(['status' => 'removed', 'gym_id' => $gymId]);
    } else {
        $ins = $db->prepare("INSERT INTO favorites (user_id, gym_id) VALUES (?, ?)");
        $ins->execute([$user['id'], $gymId]);
        echo json_encode(['status' => 'added', 'gym_id' => $gymId]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

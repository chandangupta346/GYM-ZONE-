<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'unauthorized', 'message' => 'User not logged in']);
    exit;
}

$user_id = getUserId();
$gym_id = isset($_POST['gym_id']) ? (int)$_POST['gym_id'] : 0;

if ($gym_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid gym ID']);
    exit;
}

try {
    // Check if already favorited
    $stmtCheck = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND gym_id = ?");
    $stmtCheck->execute([$user_id, $gym_id]);
    $fav = $stmtCheck->fetch();

    if ($fav) {
        // Remove favorite
        $stmtDel = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
        $stmtDel->execute([$fav['id']]);
        echo json_encode(['status' => 'success', 'favorited' => false]);
    } else {
        // Add favorite
        $stmtIns = $pdo->prepare("INSERT INTO favorites (user_id, gym_id) VALUES (?, ?)");
        $stmtIns->execute([$user_id, $gym_id]);
        echo json_encode(['status' => 'success', 'favorited' => true]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

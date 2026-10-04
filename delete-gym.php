<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$gym_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = getUserId();
$user_role = getUserRole();

if ($gym_id > 0) {
    try {
        // Verify gym ownership or admin privileges
        $stmtCheck = $pdo->prepare("SELECT owner_id FROM gyms WHERE id = ?");
        $stmtCheck->execute([$gym_id]);
        $gymOwnerId = $stmtCheck->fetchColumn();

        if ($gymOwnerId && ($gymOwnerId == $user_id || $user_role === 'admin')) {
            $stmtDel = $pdo->prepare("DELETE FROM gyms WHERE id = ?");
            $stmtDel->execute([$gym_id]);
            setFlash('success', 'Gym listing deleted successfully.');
        } else {
            setFlash('error', 'Unauthorized action.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Error deleting gym: ' . $e->getMessage());
    }
}

if ($user_role === 'admin') {
    header('Location: admin-dashboard.php');
} else {
    header('Location: owner-dashboard.php');
}
exit;
?>

<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('admin');

$gym_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($gym_id > 0 && in_array($action, ['approve', 'reject'])) {
    try {
        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';
        $stmt = $pdo->prepare("UPDATE gyms SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $gym_id]);

        setFlash('success', 'Gym listing status updated to ' . ucfirst($newStatus));
    } catch (PDOException $e) {
        setFlash('error', 'Error updating status: ' . $e->getMessage());
    }
}

header('Location: admin-dashboard.php');
exit;
?>

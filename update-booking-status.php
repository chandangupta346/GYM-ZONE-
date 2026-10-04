<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $status = isset($_POST['status']) && in_array($_POST['status'], ['pending', 'confirmed', 'cancelled']) ? $_POST['status'] : '';

    if ($booking_id > 0 && !empty($status)) {
        try {
            // Verify booking permission
            $user_id = getUserId();
            $user_role = getUserRole();

            if ($user_role === 'admin') {
                $stmtUpd = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $stmtUpd->execute([$status, $booking_id]);
                setFlash('success', 'Booking status updated to ' . ucfirst($status));
            } else {
                // Check if booking belongs to gym owned by current user
                $stmtCheck = $pdo->prepare("
                    SELECT b.id 
                    FROM bookings b 
                    JOIN gyms g ON b.gym_id = g.id 
                    WHERE b.id = ? AND g.owner_id = ?
                ");
                $stmtCheck->execute([$booking_id, $user_id]);
                if ($stmtCheck->rowCount() > 0) {
                    $stmtUpd = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                    $stmtUpd->execute([$status, $booking_id]);
                    setFlash('success', 'Booking status updated to ' . ucfirst($status));
                } else {
                    setFlash('error', 'Unauthorized to update this booking.');
                }
            }
        } catch (PDOException $e) {
            setFlash('error', 'Error updating booking: ' . $e->getMessage());
        }
    }
}

if (getUserRole() === 'admin') {
    header('Location: admin-dashboard.php');
} else {
    header('Location: owner-dashboard.php');
}
exit;
?>

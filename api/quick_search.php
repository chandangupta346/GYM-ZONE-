<?php
// api/quick_search.php - Fast JSON endpoint for search suggestions
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

$term = trim($_GET['q'] ?? '');
if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, area, starting_price, main_image 
                          FROM gyms 
                          WHERE status = 'approved' AND (name LIKE ? OR area LIKE ?) 
                          LIMIT 5");
    $param = "%$term%";
    $stmt->execute([$param, $param]);
    $results = $stmt->fetchAll();

    echo json_encode($results);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

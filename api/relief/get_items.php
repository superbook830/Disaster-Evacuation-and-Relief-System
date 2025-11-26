<?php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');
$response = ['success' => false, 'items' => []];

// Admin-only bouncer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode($response);
    exit;
}

try {
    // Select items that are not "soft-deleted"
    $query = "SELECT id, item_name, description, unit_of_measure, stock_quantity 
              FROM relief_items 
              WHERE is_deleted = 0 
              ORDER BY item_name";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $response['items'][] = $row;
    }
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
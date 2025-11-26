<?php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');
$response = ['success' => false, 'item' => null, 'log' => []];

// Admin-only bouncer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $response['message'] = 'Unauthorized';
    echo json_encode($response);
    exit;
}

// "No Loophole" Fix: Look for 'item_id' (from relief.js) not 'id'
$item_id = $_GET['item_id'] ?? 0;
if ($item_id == 0) {
    $response['message'] = 'No item ID provided.';
    echo json_encode($response);
    exit;
}

try {
    // 1. Get Item Details
    $stmt = $conn->prepare("SELECT item_name, stock_quantity, unit_of_measure FROM relief_items WHERE id = ? AND is_deleted = 0");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $response['item'] = $result->fetch_assoc();
    $stmt->close();

    if (!$response['item']) {
        throw new Exception("Item not found or has been deleted.");
    }

    // 2. Get Distribution Log (last 10 for this item)
    $stmt = $conn->prepare(
        "SELECT d.quantity, d.distribution_date, h.household_head_name 
         FROM relief_distribution d
         JOIN households h ON d.household_id = h.id
         WHERE d.item_id = ?
         ORDER BY d.distribution_date DESC
         LIMIT 10"
    );
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $log_result = $stmt->get_result();
    while ($row = $log_result->fetch_assoc()) {
        $response['log'][] = $row;
    }
    $stmt->close();
    
    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
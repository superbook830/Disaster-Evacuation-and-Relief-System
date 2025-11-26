<?php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An error occurred.'];

// Admin-only bouncer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $response['message'] = 'Unauthorized';
    echo json_encode($response);
    exit;
}

$item_id = $_POST['item_id'] ?? 0;
$item_name = $_POST['item_name'] ?? '';
$unit = $_POST['unit_of_measure'] ?? '';
$quantity = $_POST['stock_quantity'] ?? 0;
$description = $_POST['description'] ?? '';

if (empty($item_name) || !is_numeric($quantity) || empty($item_id)) {
    $response['message'] = 'Item name, a valid quantity, and an ID are required.';
    echo json_encode($response);
    exit;
}

try {
    $stmt = $conn->prepare(
        "UPDATE relief_items 
         SET item_name = ?, description = ?, unit_of_measure = ?, stock_quantity = ? 
         WHERE id = ?"
    );
    $stmt->bind_param("sssii", $item_name, $description, $unit, $quantity, $item_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Item updated successfully!';
    } else {
        $response['message'] = 'Failed to update item.';
    }
    $stmt->close();
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
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

$item_name = $_POST['item_name'] ?? '';
$unit = $_POST['unit_of_measure'] ?? '';
$quantity = $_POST['stock_quantity'] ?? 0;
$description = $_POST['description'] ?? '';

if (empty($item_name) || !is_numeric($quantity)) {
    $response['message'] = 'Item name and a valid quantity are required.';
    echo json_encode($response);
    exit;
}

try {
    $stmt = $conn->prepare(
        "INSERT INTO relief_items (item_name, description, unit_of_measure, stock_quantity) 
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("sssi", $item_name, $description, $unit, $quantity);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Item added successfully!';
    } else {
        $response['message'] = 'Failed to add item.';
    }
    $stmt->close();
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
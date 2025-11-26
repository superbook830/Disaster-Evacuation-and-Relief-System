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

if (empty($item_id)) {
    $response['message'] = 'Item ID is required.';
    echo json_encode($response);
    exit;
}

try {
    // "No Loophole" Fix:
    // Use 'is_deleted = 1' (soft delete)
    // NOT 'is_active = 0'
    $stmt = $conn->prepare("UPDATE relief_items SET is_deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Item deleted successfully!';
    } else {
        $response['message'] = 'Failed to delete item.';
    }
    $stmt->close();
} catch (Exception $e) {
    // Check for foreign key constraint fail (if item is in use)
    if ($e->getCode() == 1451) {
         $response['message'] = 'Cannot delete item: It has already been distributed. You can set its stock to 0 instead.';
    } else {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

$conn->close();
echo json_encode($response);
?>
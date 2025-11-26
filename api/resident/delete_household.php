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

// --- THIS IS THE FIXED LINE ---
// We are now correctly getting 'id' from the JavaScript
$household_id = $_POST['id'] ?? 0;

if (empty($household_id)) {
    $response['message'] = 'Household ID is required.';
    echo json_encode($response);
    exit;
}

// "No Loophole" Transaction:
// We must delete the household AND its residents.
$conn->begin_transaction();

try {
    // Step 1: Soft-delete all residents in this household
    $stmt = $conn->prepare("UPDATE residents SET is_deleted = 1 WHERE household_id = ?");
    $stmt->bind_param("i", $household_id);
    $stmt->execute();
    $stmt->close();

    // Step 2: Soft-delete the household itself
    $stmt = $conn->prepare("UPDATE households SET is_deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $household_id);
    $stmt->execute();
    $stmt->close();
    
    // If both are successful, commit the changes
    $conn->commit();

    $response['success'] = true;
    $response['message'] = 'Household and all its members deleted.';

} catch (Exception $e) {
    // If anything fails, roll back all changes
    $conn->rollback();
    $response['message'] = 'Database error: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
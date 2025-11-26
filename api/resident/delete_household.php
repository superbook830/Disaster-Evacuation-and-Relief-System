<?php
// api/resident/delete_household.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An error occurred.'];

// 1. Admin-only bouncer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $response['message'] = 'Unauthorized';
    echo json_encode($response);
    exit;
}

// 2. Get ID
$household_id = $_POST['id'] ?? 0;

if (empty($household_id)) {
    $response['message'] = 'Household ID is required.';
    echo json_encode($response);
    exit;
}

// 3. Transaction: Soft Delete Residents AND Household
$conn->begin_transaction();

try {
    // Step A: Soft-delete all residents in this household
    // This ensures we don't have "orphan" residents
    $stmt = $conn->prepare("UPDATE residents SET is_deleted = 1 WHERE household_id = ?");
    $stmt->bind_param("i", $household_id);
    $stmt->execute();
    $stmt->close();

    // Step B: Soft-delete the household itself
    $stmt = $conn->prepare("UPDATE households SET is_deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $household_id);
    $stmt->execute();
    $stmt->close();
    
    // If both works, commit!
    $conn->commit();

    $response['success'] = true;
    $response['message'] = 'Household deleted successfully.';

} catch (Exception $e) {
    // If anything fails, undo everything
    $conn->rollback();
    $response['message'] = 'Database error: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
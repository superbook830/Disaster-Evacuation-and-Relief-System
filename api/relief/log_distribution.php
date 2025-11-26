<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// --- GET DATA ---
$item_id = $_POST['item_id'] ?? 0;
$household_ids = $_POST['household_ids'] ?? [];
$quantity_per_household = $_POST['quantity_per_household'] ?? 0;

// --- VALIDATE ---
if (empty($item_id) || empty($household_ids) || !is_array($household_ids) || $quantity_per_household <= 0) {
    $response['message'] = 'Invalid data. Missing item, households, or quantity.';
    echo json_encode($response);
    exit;
}

$num_households = count($household_ids);
$total_quantity_needed = $quantity_per_household * $num_households;

// --- START TRANSACTION ---
$conn->autocommit(FALSE); // Turn off auto-commit

try {
    // 1. Lock the item row and check stock from the correct table: 'relief_items'
    $stmt = $conn->prepare("SELECT stock_quantity FROM relief_items WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if (!$item) {
        throw new Exception("Item not found.");
    }

    $current_stock = $item['stock_quantity'];
    if ($current_stock < $total_quantity_needed) {
        throw new Exception("Insufficient stock. Need: {$total_quantity_needed}, Have: {$current_stock}");
    }

    // 2. Prepare the statement for logging into the correct table: 'relief_distribution'
    $log_stmt = $conn->prepare(
        "INSERT INTO relief_distribution (item_id, household_id, quantity) VALUES (?, ?, ?)"
    );

    // 3. Loop through each household and log the distribution
    foreach ($household_ids as $household_id) {
        $log_stmt->bind_param("iii", $item_id, $household_id, $quantity_per_household);
        if (!$log_stmt->execute()) {
            throw new Exception("Failed to log distribution for household ID: {$household_id}.");
        }
    }
    $log_stmt->close();

    // 4. Update the inventory stock in the correct table: 'relief_items'
    $new_stock = $current_stock - $total_quantity_needed;
    $update_stmt = $conn->prepare("UPDATE relief_items SET stock_quantity = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $new_stock, $item_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update item stock.");
    }
    $update_stmt->close();

    // 5. If all steps succeeded, commit the transaction
    $conn->commit();
    $response['success'] = true;
    $response['message'] = "Successfully distributed {$total_quantity_needed} items to {$num_households} households.";

} catch (Exception $e) {
    // An error occurred, roll back all changes
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

// Turn autocommit back on
$conn->autocommit(TRUE);
$conn->close();

echo json_encode($response);
?>
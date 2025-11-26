<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

$response = array('success' => false, 'message' => 'An unknown error occurred.');

// Get the specific evacuee record ID from the POST request
$evacuee_record_id = $_POST['record_id'] ?? 0;

if ($evacuee_record_id == 0) {
    $response['message'] = 'Invalid Record ID.';
} else {
    // --- Prepared Statement to Update ---
    
    // We find the record by its unique ID and set the checkout time
    // We also check that time_checked_out IS NULL, just to be safe
    // (this prevents checking out the same person twice).
    $stmt = $conn->prepare(
        "UPDATE evacuees 
         SET time_checked_out = CURRENT_TIMESTAMP 
         WHERE id = ? AND time_checked_out IS NULL"
    );

    // "i" means integer
    $stmt->bind_param("i", $evacuee_record_id);

    if ($stmt->execute()) {
        // We check if a row was actually changed
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Resident checked out successfully!';
        } else {
            $response['message'] = 'Record not found or already checked out.';
        }
    } else {
        $response['message'] = 'Database error: ' . $stmt->error;
    }

    $stmt->close();
}

$conn->close();

echo json_encode($response);
?>
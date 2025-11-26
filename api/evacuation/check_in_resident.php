<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

$response = array('success' => false, 'message' => 'An unknown error occurred.');

// Get data from the POST request (from our JavaScript)
$resident_id = $_POST['resident_id'] ?? 0;
$center_id = $_POST['center_id'] ?? 0;

if ($resident_id == 0 || $center_id == 0) {
    $response['message'] = 'Invalid Resident or Center ID.';
    echo json_encode($response);
    exit;
}

// --- "No Loophole" Transaction ---
// This ensures all checks pass before any data is saved.

// Turn off autocommit to start the transaction
$conn->autocommit(FALSE);

try {
    // 1. Check if resident is already checked in somewhere
    $stmt = $conn->prepare("SELECT id FROM evacuees WHERE resident_id = ? AND time_checked_out IS NULL LIMIT 1");
    $stmt->bind_param("i", $resident_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        throw new Exception('This resident is already checked in at another center.');
    }
    $stmt->close();

    // 2. Check if the center is full (This is the critical part)
    // We lock the row FOR UPDATE to prevent race conditions
    $stmt = $conn->prepare(
        "SELECT 
            ec.capacity, 
            (SELECT COUNT(*) FROM evacuees e WHERE e.center_id = ec.id AND e.time_checked_out IS NULL) AS current_occupancy
        FROM evacuation_centers ec 
        WHERE ec.id = ? FOR UPDATE"
    );
    $stmt->bind_param("i", $center_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $center_data = $result->fetch_assoc();
    $stmt->close();

    if (!$center_data) {
        throw new Exception('Evacuation center not found.');
    }

    if ($center_data['current_occupancy'] >= $center_data['capacity']) {
        throw new Exception('This center is full. Cannot check in resident.');
    }

    // 3. All checks passed! Insert the new evacuee record.
    $stmt = $conn->prepare(
        "INSERT INTO evacuees (resident_id, center_id) VALUES (?, ?)"
    );
    $stmt->bind_param("ii", $resident_id, $center_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to save check-in record.');
    }
    $stmt->close();

    // If all went well, commit the transaction
    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Resident checked in successfully!';

} catch (Exception $e) {
    // An error occurred. Roll back any changes.
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

// Turn autocommit back on
$conn->autocommit(TRUE);
$conn->close();

echo json_encode($response);
?>
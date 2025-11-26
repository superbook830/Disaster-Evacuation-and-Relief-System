<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php'; // ../ goes up one folder

$response = array('success' => false, 'message' => 'An unknown error occurred.');

// Get data from the POST request
$center_name = $_POST['center_name'] ?? '';
$address = $_POST['address'] ?? '';
$capacity = $_POST['capacity'] ?? 0;
$is_active = isset($_POST['is_active']) ? 1 : 0; // Checkbox value

// Validation
if (empty($center_name)) {
    $response['message'] = 'Center Name is required.';
} elseif ($capacity <= 0) {
    $response['message'] = 'Capacity must be a positive number.';
} else {
    // --- Security: Prepared Statement ---
    
    // 1. Prepare the query
    $stmt = $conn->prepare(
        "INSERT INTO evacuation_centers (center_name, address, capacity, is_active) 
         VALUES (?, ?, ?, ?)"
    );

    // 2. Bind the variables
    // "ssii" means string, string, integer, integer
    $stmt->bind_param("ssii", 
        $center_name, 
        $address, 
        $capacity,
        $is_active
    );

    // 3. Execute
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Evacuation center added successfully!';
    } else {
        // Check for a duplicate name error
        if ($conn->errno == 1062) { // 1062 is the MySQL code for "Duplicate entry"
            $response['message'] = 'Error: An evacuation center with this name already exists.';
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
    }

    // 4. Close the statement
    $stmt->close();
}

$conn->close();

echo json_encode($response);

?>
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

$response = [
    'success' => false,
    'message' => 'An unknown error occurred.'
];

// --- 1. Get the data ---
$household_head_name = trim($_POST['household_head_name'] ?? '');
$zone_purok = $_POST['zone_purok'] ?? '';
$address_notes = $_POST['address_notes'] ?? '';

// --- 2. Validation ---
if (empty($household_head_name)) {
    $response['message'] = 'Household Head Name is required.';
    echo json_encode($response);
    exit;
}

// --- 3. "Smart" Name Splitting ---
$name_parts = explode(' ', $household_head_name, 2);
$first_name = $name_parts[0];
$last_name = $name_parts[1] ?? '(no last name)'; 


// --- 4. Start Transaction ---
$conn->begin_transaction();

try {
    // --- Step A: Insert into households table ---
    $stmt1 = $conn->prepare(
        "INSERT INTO households (household_head_name, zone_purok, address_notes) 
         VALUES (?, ?, ?)"
    );
    $stmt1->bind_param("sss", $household_head_name, $zone_purok, $address_notes);
    $stmt1->execute();
    
    // Get the ID of the new household we just created
    $new_household_id = $conn->insert_id;
    $stmt1->close();

    if ($new_household_id == 0) {
        throw new Exception("Failed to create new household.");
    }

    // --- Step B: Insert the head as a person into residents table ---
    $stmt2 = $conn->prepare(
        "INSERT INTO residents (household_id, first_name, last_name) 
         VALUES (?, ?, ?)"
    );
    $stmt2->bind_param("iss", $new_household_id, $first_name, $last_name);
    $stmt2->execute();
    $stmt2->close();

    // --- Step C: If all good, commit changes ---
    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Household and Head of Family created successfully!';

} catch (Exception $e) {
    // --- Step D: If anything failed, roll back all changes ---
    $conn->rollback();
    // --- THIS IS THE FIXED LINE ---
    $response['message'] = 'Transaction Error: ' . $e->getMessage();
}

// Close the database connection
$conn->close();

// Send the JSON response back
echo json_encode($response);
?>
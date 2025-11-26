<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

$response = array('success' => false, 'message' => 'An unknown error occurred.');

// Get all the data from the form
$household_id = $_POST['household_id'] ?? 0;
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$birthdate = $_POST['birthdate'] ?? null;
$gender = $_POST['gender'] ?? 'other';
$remarks = $_POST['remarks'] ?? '';

// Handle checkboxes (a "no loophole" step)
// If the checkbox was ticked, $_POST['is_pwd'] will be '1'. If not, it won't exist.
$is_pwd = isset($_POST['is_pwd']) ? 1 : 0;
$is_senior = isset($_POST['is_senior']) ? 1 : 0;

// Validation
if (empty($first_name) || empty($last_name)) {
    $response['message'] = 'First Name and Last Name are required.';
} elseif ($household_id == 0) {
    $response['message'] = 'Invalid Household ID.';
} else {
    // Make 'birthdate' NULL if it was empty
    if (empty($birthdate)) {
        $birthdate = null;
    }

    // Prepared Statement
    $stmt = $conn->prepare(
        "INSERT INTO residents 
         (household_id, first_name, last_name, birthdate, gender, is_pwd, is_senior, remarks) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    
    // "isssssis" - i: integer, s: string
    $stmt->bind_param("isssssis", 
        $household_id, 
        $first_name, 
        $last_name, 
        $birthdate, 
        $gender,
        $is_pwd,
        $is_senior,
        $remarks
    );

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'New resident added successfully!';
    } else {
        $response['message'] = 'Database error: ' . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
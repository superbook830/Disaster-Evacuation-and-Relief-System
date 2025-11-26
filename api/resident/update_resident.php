<?php
include '../config/db_connect.php';
header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'An error occurred.');

$id = $_POST['id'] ?? 0;
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$birthdate = $_POST['birthdate'] ?? null;
$gender = $_POST['gender'] ?? 'other';
$remarks = $_POST['remarks'] ?? '';

// Checkboxes are tricky. If unchecked, they aren't sent.
$is_pwd = isset($_POST['is_pwd']) ? 1 : 0;
$is_senior = isset($_POST['is_senior']) ? 1 : 0;

if ($id > 0 && !empty($first_name) && !empty($last_name)) {
    $stmt = $conn->prepare("UPDATE residents 
                           SET first_name = ?, last_name = ?, birthdate = ?, gender = ?, 
                               is_pwd = ?, is_senior = ?, remarks = ? 
                           WHERE id = ?");
    
    // "sssiiisi" = string, string, string, integer, integer, integer, string, integer
    $stmt->bind_param("ssssiisi", 
        $first_name, 
        $last_name, 
        $birthdate, 
        $gender, 
        $is_pwd, 
        $is_senior, 
        $remarks, 
        $id
    );

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Resident updated successfully!';
    } else {
        $response['message'] = 'Error updating resident: ' . $stmt->error;
    }
    $stmt->close();
} else {
    $response['message'] = 'Invalid data provided (ID, First Name, Last Name are required).';
}

$conn->close();
echo json_encode($response);
?>
<?php
include '../config/db_connect.php';
header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'An error occurred.');

$id = $_POST['id'] ?? 0;
$head_name = $_POST['household_head_name'] ?? '';
$zone = $_POST['zone_purok'] ?? '';
$address = $_POST['address_notes'] ?? '';

if ($id > 0 && !empty($head_name)) {
    $stmt = $conn->prepare("UPDATE households 
                           SET household_head_name = ?, zone_purok = ?, address_notes = ? 
                           WHERE id = ?");
    $stmt->bind_param("sssi", $head_name, $zone, $address, $id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Household updated successfully!';
    } else {
        $response['message'] = 'Error updating household: ' . $stmt->error;
    }
    $stmt->close();
} else {
    $response['message'] = 'Invalid data provided (ID and Head Name are required).';
}

$conn->close();
echo json_encode($response);
?>
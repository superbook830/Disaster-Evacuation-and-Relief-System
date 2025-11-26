<?php
include '../config/db_connect.php';
header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'An error occurred.');

$resident_id = $_POST['id'] ?? 0;

if ($resident_id > 0) {
    // "No Loophole" Soft Delete
    $stmt = $conn->prepare("UPDATE residents SET is_deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $resident_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Resident deleted successfully.';
    } else {
        $response['message'] = 'Error deleting resident: ' . $stmt->error;
    }
    $stmt->close();
} else {
    $response['message'] = 'Invalid Resident ID.';
}

$conn->close();
echo json_encode($response);
?>
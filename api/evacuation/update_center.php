<?php
header("Content-Type: application/json");
include_once '../config/db_connect.php';

$id = $_POST['id'] ?? '';
$center_name = $_POST['center_name'] ?? '';
$address = $_POST['address'] ?? '';
$capacity = $_POST['capacity'] ?? '';
$is_active = isset($_POST['is_active']) ? 1 : 0;

if (!$id || !$center_name || !$capacity) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$query = "UPDATE evacuation_centers SET center_name=?, address=?, capacity=?, is_active=? WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssiii", $center_name, $address, $capacity, $is_active, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Center updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed"]);
}

$stmt->close();
$conn->close();
?>

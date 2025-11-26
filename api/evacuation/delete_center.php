<?php
header("Content-Type: application/json");
include_once '../config/db_connect.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Missing ID"]);
    exit;
}

$query = "DELETE FROM evacuation_centers WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Center deleted successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete center"]);
}
$stmt->close();
$conn->close();
?>

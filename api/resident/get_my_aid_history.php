<?php
// api/resident/get_my_aid_history.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

// 1. Check Login
if (!isset($_SESSION['resident_id'])) {
    echo json_encode([]); 
    exit;
}

$current_resident_id = $_SESSION['resident_id'];

// 2. Get the Household ID of the current user
$stmt = $conn->prepare("SELECT household_id FROM residents WHERE id = ?");
$stmt->bind_param("i", $current_resident_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$household_id = $row['household_id'] ?? null;
$stmt->close();

if (!$household_id) {
    echo json_encode([]); 
    exit;
}

// 3. Get Aid History (FIXED FOR YOUR DATABASE)
// Joined table: relief_distribution (rd) + relief_items (ri)
// FIX: Changed 'ri.name' to 'ri.item_name' based on your screenshot

$sql = "SELECT 
            ri.item_name as item_name, 
            rd.quantity,
            rd.distribution_date as date_distributed
        FROM 
            relief_distribution rd
        JOIN 
            relief_items ri ON rd.item_id = ri.id
        WHERE 
            rd.household_id = ?
        ORDER BY 
            rd.distribution_date DESC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $household_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = array();
    while($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    echo json_encode($history);
    $stmt->close();
} else {
    // Return empty array on error
    echo json_encode([]);
}

$conn->close();
?>
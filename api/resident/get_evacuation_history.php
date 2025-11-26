<?php
// api/resident/get_evacuation_history.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

// 1. Check Login
if (!isset($_SESSION['resident_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$current_resident_id = $_SESSION['resident_id'];

// 2. Get Household ID
$stmt = $conn->prepare("SELECT household_id FROM residents WHERE id = ?");
$stmt->bind_param("i", $current_resident_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$household_id = $row['household_id'] ?? null;
$stmt->close();

if (!$household_id) {
    echo json_encode(['success' => false, 'message' => 'No household found.']);
    exit;
}

// 3. Fetch History (FIXED)
// FIX: Changed 'ec.name' to 'ec.center_name' based on your screenshot

$sql = "SELECT 
            r.first_name, 
            r.last_name, 
            ec.center_name as center_name, 
            e.time_checked_in as check_in_time, 
            e.time_checked_out as check_out_time,
            CASE 
                WHEN e.time_checked_out IS NULL THEN 'Active'
                ELSE 'Checked Out'
            END as status
        FROM 
            evacuees e
        JOIN 
            residents r ON e.resident_id = r.id
        JOIN 
            evacuation_centers ec ON e.center_id = ec.id
        WHERE 
            r.household_id = ?
        ORDER BY 
            e.time_checked_in DESC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $household_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = array();
    while($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    echo json_encode(['success' => true, 'history' => $history]);
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $conn->error]);
}

$conn->close();
?>
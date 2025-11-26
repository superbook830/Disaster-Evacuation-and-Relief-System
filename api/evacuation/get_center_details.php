<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

// We get the ID from the URL, e.g., ...?id=1
$center_id = $_GET['id'] ?? 0;

$response = array(
    'center_info' => null,
    'current_evacuees' => []
);

// --- 1. Get Center Info & Current Occupancy ---
// This gets the center's name, capacity, and calculates current occupancy
$stmt = $conn->prepare(
    "SELECT 
        ec.id, 
        ec.center_name, 
        ec.capacity,
        (SELECT COUNT(*) 
         FROM evacuees e 
         WHERE e.center_id = ec.id AND e.time_checked_out IS NULL) AS current_occupancy
    FROM 
        evacuation_centers ec
    WHERE 
        ec.id = ?"
);
$stmt->bind_param("i", $center_id); // "i" means integer
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $response['center_info'] = $result->fetch_assoc();
}
$stmt->close();


// --- 2. Get List of Current Evacuees ---
// This joins 'evacuees' with 'residents' to get the names
$stmt = $conn->prepare(
    "SELECT 
        e.id as evacuee_record_id, 
        r.id as resident_id, 
        r.first_name, 
        r.last_name, 
        e.time_checked_in
    FROM 
        evacuees e
    JOIN 
        residents r ON e.resident_id = r.id
    WHERE 
        e.center_id = ? AND e.time_checked_out IS NULL
    ORDER BY 
        r.last_name, r.first_name"
);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $response['current_evacuees'][] = $row;
    }
}
$stmt->close();

$conn->close();

echo json_encode($response);
?>
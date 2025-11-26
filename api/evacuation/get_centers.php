<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

$centers = array();

// This is an advanced SQL query.
// It joins the 'evacuation_centers' table with a "subquery"
// that counts the 'current_occupancy' from the 'evacuees' table.
$sql = "SELECT 
            ec.id, 
            ec.center_name, 
            ec.address, 
            ec.capacity, 
            ec.is_active,
            (SELECT COUNT(*) 
             FROM evacuees e 
             WHERE e.center_id = ec.id AND e.time_checked_out IS NULL) AS current_occupancy
        FROM 
            evacuation_centers ec
        ORDER BY 
            ec.center_name ASC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // Loop through each center
    while($row = $result->fetch_assoc()) {
        
        // Calculate remaining capacity in PHP
        $row['remaining_capacity'] = (int)$row['capacity'] - (int)$row['current_occupancy'];
        
        // Add the row to our response array
        $centers[] = $row;
    }
}

$conn->close();

// Send the JSON response
echo json_encode($centers);

?>
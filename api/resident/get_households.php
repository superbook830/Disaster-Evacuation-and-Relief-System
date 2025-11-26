<?php
// api/resident/get_households.php

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

$households = array();

// UPDATED QUERY: Added 'h.latitude' and 'h.longitude'
$sql = "SELECT 
            h.id, 
            h.household_head_name, 
            h.zone_purok, 
            h.address_notes, 
            h.latitude, 
            h.longitude,
            COUNT(r.id) as member_count
        FROM 
            households h
        LEFT JOIN 
            residents r ON h.id = r.household_id
        WHERE 
            h.is_deleted = 0
        GROUP BY 
            h.id
        ORDER BY 
            h.household_head_name ASC";

$result = $conn->query($sql);

if ($result) {
    while($row = $result->fetch_assoc()) {
        $households[] = $row;
    }
} else {
    // Log error if needed
    // error_log("SQL Error: " . $conn->error);
}

$conn->close();

echo json_encode($households);
?>
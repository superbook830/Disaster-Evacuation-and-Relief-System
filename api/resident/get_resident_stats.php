<?php
// api/resident/get_resident_stats.php

// 1. Turn OFF display errors so we don't break the JSON format
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

// Default response
$response = [
    "total_households" => 0,
    "total_residents" => 0,
    "affected_households" => 0,
    "residents_evacuated" => 0
];

if ($conn->connect_error) {
    echo json_encode($response);
    exit;
}

// --- 1. TOTAL HOUSEHOLDS ---
$sql = "SELECT COUNT(*) AS total FROM households";
$result = $conn->query($sql);
if ($result) {
    $response["total_households"] = (int)$result->fetch_assoc()["total"];
}

// --- 2. TOTAL RESIDENTS ---
$sql = "SELECT COUNT(*) AS total FROM residents";
$result = $conn->query($sql);
if ($result) {
    $response["total_residents"] = (int)$result->fetch_assoc()["total"];
}

// --- 3. RESIDENTS EVACUATED (Active) ---
// People currently in evacuation centers (no check-out time)
$sql = "SELECT COUNT(*) AS total FROM evacuees WHERE time_checked_out IS NULL";
$result = $conn->query($sql);
if ($result) {
    $response["residents_evacuated"] = (int)$result->fetch_assoc()["total"];
}

// --- 4. AFFECTED HOUSEHOLDS ---
// Households that have at least one member currently evacuated
$sql = "SELECT COUNT(DISTINCT r.household_id) AS total 
        FROM evacuees e 
        JOIN residents r ON e.resident_id = r.id 
        WHERE e.time_checked_out IS NULL";
$result = $conn->query($sql);
if ($result) {
    $response["affected_households"] = (int)$result->fetch_assoc()["total"];
}

echo json_encode($response);
$conn->close();
?>
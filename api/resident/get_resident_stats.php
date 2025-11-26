<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed: " . $conn->connect_error]);
    exit;
}

$response = [
    "total_households" => 0,
    "total_residents" => 0,
    "affected_households" => 0,
    "residents_evacuated" => 0,
    "debug_errors" => []
];

// --- 1️⃣ TOTAL HOUSEHOLDS ---
$result = $conn->query("SELECT COUNT(*) AS total FROM households WHERE is_deleted = 0");
if ($result) {
    $response["total_households"] = (int)$result->fetch_assoc()["total"];
} else {
    $response["debug_errors"]["households"] = $conn->error;
}

// --- 2️⃣ TOTAL RESIDENTS ---
$result = $conn->query("SELECT COUNT(*) AS total FROM residents WHERE is_deleted = 0");
if ($result) {
    $response["total_residents"] = (int)$result->fetch_assoc()["total"];
} else {
    $response["debug_errors"]["residents"] = $conn->error;
}

// --- 3️⃣ AFFECTED HOUSEHOLDS ---
$query = "SELECT COUNT(DISTINCT h.id) AS total
          FROM households h
          JOIN residents r ON r.household_id = h.id
          JOIN evacuees e ON e.resident_id = r.id
          WHERE e.time_checked_out IS NULL AND h.is_deleted = 0 AND r.is_deleted = 0";
$result = $conn->query($query);
if ($result) {
    $response["affected_households"] = (int)$result->fetch_assoc()["total"];
} else {
    $response["debug_errors"]["affected"] = $conn->error;
}

// --- 4️⃣ RESIDENTS EVACUATED ---
$result = $conn->query("SELECT COUNT(*) AS total FROM evacuees WHERE time_checked_out IS NULL");
if ($result) {
    $response["residents_evacuated"] = (int)$result->fetch_assoc()["total"];
} else {
    $response["debug_errors"]["evacuated"] = $conn->error;
}

echo json_encode($response);
?>
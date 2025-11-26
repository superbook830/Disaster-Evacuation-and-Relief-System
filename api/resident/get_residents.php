<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

$household_id = $_GET['id'] ?? 0;

$response = array(
    'household_info' => null,
    'residents' => []
);

if ($household_id > 0) {
    // --- Get Household Info ---
    // UPDATED: Added "WHERE is_deleted = 0"
    $stmt = $conn->prepare("SELECT id, household_head_name 
                           FROM households 
                           WHERE id = ? AND is_deleted = 0");
    $stmt->bind_param("i", $household_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $response['household_info'] = $result->fetch_assoc();
    }
    $stmt->close();

    // --- Get Residents in that Household ---
    // UPDATED: Added "WHERE r.is_deleted = 0"
    $stmt = $conn->prepare(
        "SELECT id, first_name, last_name, birthdate, gender, is_pwd, is_senior, remarks 
         FROM residents r
         WHERE household_id = ? AND r.is_deleted = 0
         ORDER BY last_name, first_name"
    );
    $stmt->bind_param("i", $household_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $response['residents'][] = $row;
        }
    }
    $stmt->close();
}

$conn->close();

echo json_encode($response);
?>
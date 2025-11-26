<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

$results = [];
// Get the search term from the URL (e.g., ...?term=Doe)
$term = $_GET['term'] ?? '';

if (!empty($term)) {
    // Add the wildcard '%'
    $searchTerm = $term . '%';

    // A simple search on the households table
    $stmt = $conn->prepare(
        "SELECT 
            id, household_head_name, zone_purok
        FROM 
            households
        WHERE 
            household_head_name LIKE ?
        ORDER BY 
            household_head_name
        LIMIT 10" // Limit to 10 results
    );
    
    // "s" = one string parameter
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
    $stmt->close();
}

$conn->close();
echo json_encode($results);
?>
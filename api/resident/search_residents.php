<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

$results = [];
// Get the search term from the URL (e.g., ...?term=Smith)
$term = $_GET['term'] ?? '';

if (!empty($term)) {
    // Add the wildcard '%' to the end of the term
    $searchTerm = $term . '%';

    // This is our "no loophole" query.
    // 1. It JOINS residents (r) with households (h) to get the household head's name.
    // 2. It LEFT JOINS evacuees (e) on the resident ID AND where time_checked_out IS NULL.
    //    This finds any "active" check-in record.
    // 3. The "WHERE e.id IS NULL" clause is the magic: it *only* returns residents
    //    who do NOT have an active check-in record.
    $stmt = $conn->prepare(
        "SELECT 
            r.id, r.first_name, r.last_name, h.household_head_name
        FROM 
            residents r
        JOIN 
            households h ON r.household_id = h.id
        LEFT JOIN 
            evacuees e ON r.id = e.resident_id AND e.time_checked_out IS NULL
        WHERE 
            (r.last_name LIKE ? OR r.first_name LIKE ?) AND e.id IS NULL
        ORDER BY 
            r.last_name, r.first_name
        LIMIT 10" // Limit to 10 results for performance
    );
    
    // "ss" = two string parameters
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
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
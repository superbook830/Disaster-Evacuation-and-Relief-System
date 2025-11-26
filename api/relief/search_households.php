<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

// Get the search term from the JavaScript (e.g., ...?search=marl)
$term = $_GET['search'] ?? '';

// This is the wrapper that your distribute_aid.js file expects
$response = [
    'success' => false,
    'households' => [],
    'message' => 'No search term provided.'
];

if (!empty($term)) {
    // Add the wildcard '%'
    $searchTerm = $term . '%';

    // --- UPDATED SQL QUERY ---
    // This query now:
    // 1. Joins with the `residents` table to get a member count.
    // 2. Selects all the data your new search card needs.
    $stmt = $conn->prepare(
        "SELECT 
            h.id, 
            h.household_head_name, 
            h.zone_purok,
            h.address_notes,
            COUNT(r.id) AS member_count
        FROM 
            households h
        LEFT JOIN 
            residents r ON h.id = r.household_id AND r.is_deleted = 0
        WHERE 
            h.household_head_name LIKE ? 
            AND h.is_deleted = 0
        GROUP BY 
            h.id
        ORDER BY 
            h.household_head_name
        LIMIT 10"
    );
    
    // "s" = one string parameter
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Add each row to the 'households' array
            $response['households'][] = $row;
        }
        $response['success'] = true;
        $response['message'] = $result->num_rows . ' household(s) found.';
    } else {
        $response['success'] = false; // Not an error, just no results
        $response['message'] = 'No households found matching that name.';
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
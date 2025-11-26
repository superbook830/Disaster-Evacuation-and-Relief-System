<?php
// api/resident/update_household.php
include_once '../config/session.php'; // Added Session for security
include_once '../config/db_connect.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'An error occurred.');

// 1. Check Admin Access (Security)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// 2. Get Data
$id = $_POST['id'] ?? 0;
$head_name = $_POST['household_head_name'] ?? '';
$zone = $_POST['zone_purok'] ?? '';
$address = $_POST['address_notes'] ?? '';
$lat = $_POST['latitude'] ?? null;
$lng = $_POST['longitude'] ?? null;

// Handle empty strings for coordinates (convert to NULL)
if ($lat === '') $lat = null;
if ($lng === '') $lng = null;

if ($id > 0 && !empty($head_name)) {
    
    // 3. Update Query (Now includes Latitude and Longitude)
    $stmt = $conn->prepare("UPDATE households 
                            SET household_head_name = ?, 
                                zone_purok = ?, 
                                address_notes = ?, 
                                latitude = ?, 
                                longitude = ?
                            WHERE id = ?");
                            
    // "sssddi" = String, String, String, Double, Double, Integer
    $stmt->bind_param("sssddi", $head_name, $zone, $address, $lat, $lng, $id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Household and location updated successfully!';
    } else {
        $response['message'] = 'Error updating household: ' . $stmt->error;
    }
    $stmt->close();
} else {
    $response['message'] = 'Invalid data provided (ID and Head Name are required).';
}

$conn->close();
echo json_encode($response);
?>
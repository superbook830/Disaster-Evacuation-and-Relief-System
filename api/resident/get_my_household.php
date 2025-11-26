<?php
// api/resident/get_my_household.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['resident_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$current_resident_id = $_SESSION['resident_id'];

// 2. Find the household ID for this user
$stmt = $conn->prepare("SELECT household_id FROM residents WHERE id = ?");
$stmt->bind_param("i", $current_resident_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Resident profile not found.']);
    exit;
}

$row = $result->fetch_assoc();
$household_id = $row['household_id'];
$stmt->close();

if (!$household_id) {
    echo json_encode(['success' => false, 'message' => 'No household assigned.']);
    exit;
}

// 3. Get the Household Details
// We select 'address_notes' because that is the column in your database
$hh_sql = "SELECT household_head_name, address_notes, zone_purok FROM households WHERE id = ?";
$stmt_hh = $conn->prepare($hh_sql);
$stmt_hh->bind_param("i", $household_id);
$stmt_hh->execute();
$hh_data = $stmt_hh->get_result()->fetch_assoc();
$stmt_hh->close();

// 4. Get the Family Members
$mem_sql = "SELECT first_name, last_name, birthdate, gender FROM residents WHERE household_id = ?";
$stmt_mem = $conn->prepare($mem_sql);
$stmt_mem->bind_param("i", $household_id);
$stmt_mem->execute();
$mem_result = $stmt_mem->get_result();

$members = array();
while($m = $mem_result->fetch_assoc()) {
    $members[] = $m;
}
$stmt_mem->close();

// 5. Return the data in the format the Dashboard expects
echo json_encode([
    'success' => true,
    'household' => $hh_data,
    'members' => $members
]);

$conn->close();
?>
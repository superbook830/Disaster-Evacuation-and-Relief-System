<?php
// api/resident/update_member.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['resident_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get Data
$member_id = $_POST['member_id'] ?? 0;
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$birthdate = $_POST['birthdate'] ?? null;
$gender = $_POST['gender'] ?? 'Other';
$remarks = $_POST['remarks'] ?? '';
$is_pwd = isset($_POST['is_pwd']) ? 1 : 0;
$is_senior = isset($_POST['is_senior']) ? 1 : 0;

if (empty($member_id) || empty($first_name) || empty($last_name)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

// SECURITY: Ensure this member actually belongs to the user's household
$current_resident_id = $_SESSION['resident_id'];

// Get User's Household ID
$stmt = $conn->prepare("SELECT household_id FROM residents WHERE id = ?");
$stmt->bind_param("i", $current_resident_id);
$stmt->execute();
$hh_id = $stmt->get_result()->fetch_assoc()['household_id'];
$stmt->close();

// UPDATE Query (Only if household_id matches)
$sql = "UPDATE residents 
        SET first_name=?, last_name=?, birthdate=?, gender=?, remarks=?, is_pwd=?, is_senior=? 
        WHERE id=? AND household_id=?";

$stmt = $conn->prepare($sql);
if (empty($birthdate)) $birthdate = null;

$stmt->bind_param("sssssiiii", $first_name, $last_name, $birthdate, $gender, $remarks, $is_pwd, $is_senior, $member_id, $hh_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $conn->error]);
}

$conn->close();
?>
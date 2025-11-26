<?php
// api/resident/delete_member.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['resident_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$member_id = $_POST['member_id'] ?? 0;
$current_resident_id = $_SESSION['resident_id'];

// Prevent user from deleting THEMSELVES
if ($member_id == $current_resident_id) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account here.']);
    exit;
}

// Get User's Household ID
$stmt = $conn->prepare("SELECT household_id FROM residents WHERE id = ?");
$stmt->bind_param("i", $current_resident_id);
$stmt->execute();
$hh_id = $stmt->get_result()->fetch_assoc()['household_id'];
$stmt->close();

// DELETE Query (Only if household_id matches)
$sql = "DELETE FROM residents WHERE id=? AND household_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $member_id, $hh_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Delete failed.']);
}

$conn->close();
?>
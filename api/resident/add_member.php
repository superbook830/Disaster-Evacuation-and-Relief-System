<?php
// api/resident/add_member.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// 1. Check Login
if (!isset($_SESSION['resident_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit;
}

// 2. Get Input Data
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$birthdate = $_POST['birthdate'] ?? null;
$gender = $_POST['gender'] ?? 'Not Specified';
$remarks = $_POST['remarks'] ?? '';

// Handle Checkboxes (0 or 1)
$is_pwd = isset($_POST['is_pwd']) ? 1 : 0;
$is_senior = isset($_POST['is_senior']) ? 1 : 0;

if (empty($first_name) || empty($last_name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required.']);
    exit;
}

// 3. Find the User's Household ID
$current_resident_id = $_SESSION['resident_id'];
$stmt = $conn->prepare("SELECT household_id FROM residents WHERE id = ?");
$stmt->bind_param("i", $current_resident_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$household_id = $row['household_id'] ?? null;
$stmt->close();

if (!$household_id) {
    echo json_encode(['success' => false, 'message' => 'No household found for this user.']);
    exit;
}

// 4. Insert the New Member (Matching Admin Logic)
$sql = "INSERT INTO residents 
        (household_id, first_name, last_name, birthdate, gender, is_pwd, is_senior, remarks) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (empty($birthdate)) $birthdate = null;

// Binding parameters:
// i = integer (household_id)
// s = string (first_name)
// s = string (last_name)
// s = string (birthdate)
// s = string (gender)
// i = integer (is_pwd) - Using integer for safety
// i = integer (is_senior)
// s = string (remarks)
$stmt->bind_param("issssiis", $household_id, $first_name, $last_name, $birthdate, $gender, $is_pwd, $is_senior, $remarks);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Family member added successfully!';
} else {
    $response['message'] = 'Database Error: ' . $conn->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
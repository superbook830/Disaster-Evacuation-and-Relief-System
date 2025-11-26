<?php
// Include the new session manager
include_once '../config/session.php'; 
include_once '../config/db_connect.php';

header('Content-Type: application/json');
$response = array('success' => false, 'message' => 'An unknown error occurred.');

// --- Get data from YOUR form ---
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$address = $_POST['address'] ?? ''; 
$email = $_POST['email'] ?? ''; 
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? ''; 

// --- Validation ---
if (empty($first_name) || empty($last_name) || empty($address) || empty($email) || empty($password)) {
    $response['message'] = 'All fields are required.';
    echo json_encode($response);
    exit;
}

if ($password !== $password_confirm) {
    $response['message'] = 'Passwords do not match.';
    echo json_encode($response);
    exit;
}

// Check if username (email) already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $response['message'] = 'That email (username) is already registered.';
    $stmt->close();
    echo json_encode($response);
    exit;
}
$stmt->close();

// --- Database Transaction ---
$conn->begin_transaction();

try {
    // --- Step 1: Create the Household (FIXED for YOUR DB) ---
    $full_name = $first_name . ' ' . $last_name;
    
    // FIX: Using 'address_notes' because that is what your DB has.
    // FIX: Removed 'member_count' because your DB doesn't need it.
    $stmt_hh = $conn->prepare("INSERT INTO households (household_head_name, address_notes) VALUES (?, ?)");
    $stmt_hh->bind_param("ss", $full_name, $address); 
    $stmt_hh->execute();
    $household_id = $conn->insert_id;
    $stmt_hh->close();

    // --- Step 2: Create the Resident ---
    // Links the resident to the household we just created
    $stmt_res = $conn->prepare("INSERT INTO residents (household_id, first_name, last_name) VALUES (?, ?, ?)");
    $stmt_res->bind_param("iss", $household_id, $first_name, $last_name);
    $stmt_res->execute();
    $resident_id = $conn->insert_id;
    $stmt_res->close();

    // --- Step 4: Create the User account ---
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'resident';
    
    $stmt_user = $conn->prepare("INSERT INTO users (username, password_hash, full_name, role, resident_id) VALUES (?, ?, ?, ?, ?)");
    $stmt_user->bind_param("ssssi", $email, $password_hash, $full_name, $role, $resident_id); 
    $stmt_user->execute();
    $user_id = $conn->insert_id;
    $stmt_user->close();

    // --- Step 5: Commit the changes ---
    $conn->commit();
    
    // --- Step 6: Log the new user in ---
    $_SESSION['user_id'] = $user_id;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['role'] = $role;
    $_SESSION['resident_id'] = $resident_id;
    $_SESSION['profile_picture_url'] = null;

    $response['success'] = true;
    $response['message'] = 'Registration successful. Logging you in...';

} catch (Exception $e) {
    // --- Fail! Roll back all changes ---
    $conn->rollback();
    $response['message'] = 'An error occurred during registration: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
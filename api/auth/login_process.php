<?php
// Include the new session manager
include_once '../config/session.php'; 

header('Content-Type: application/json');
include '../config/db_connect.php';

$response = array('success' => false, 'message' => 'An unknown error occurred.');
// ... (rest of the file is fine)

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $response['message'] = 'Username and password are required.';
    echo json_encode($response);
    exit;
}

// --- Find the user in the database ---
// UPDATED: We added "resident_id" to the query
$stmt = $conn->prepare("SELECT id, full_name, role, password_hash, profile_picture_url, resident_id 
                       FROM users 
                       WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    // User was found
    $user = $result->fetch_assoc();
    
    // --- THIS IS THE "NO LOOPHOLE" STEP ---
    if (password_verify($password, $user['password_hash'])) {
        // Password is correct!
        
        // Store user info in the session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_picture_url'] = $user['profile_picture_url'];
        
        // --- NEW! Store the Resident ID ---
        $_SESSION['resident_id'] = $user['resident_id'];
        
        $response['success'] = true;
        $response['message'] = 'Login successful! Redirecting...';
        
    } else {
        // Password was incorrect
        $response['message'] = 'Invalid username or password.';
    }
} else {
    // User was not found
    $response['message'] = 'Invalid username or password.';
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
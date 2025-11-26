<?php
// Include the session manager
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');
$response = array('success' => false, 'message' => 'An error occurred.');

// "No Loophole" Bouncer: Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Authentication required.';
    echo json_encode($response);
    exit;
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Check if a file was uploaded
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    
    $file = $_FILES['profile_pic'];

    // --- 1. File Validation ---
    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 5 * 1024 * 1024; // 5 MB

    if (!in_array($file['type'], $allowed_types)) {
        $response['message'] = 'Invalid file type. Only JPG and PNG are allowed.';
    } elseif ($file['size'] > $max_size) {
        $response['message'] = 'File is too large. Maximum size is 5 MB.';
    } else {
        
        // --- 2. Create Secure Filename & Path ---
        $upload_dir = '../../uploads/'; // Go up two levels, then into "uploads/"
        
        // Make sure the uploads directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Create a unique, secure name: "user_1_timestamp.jpg"
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;

        // --- 3. Move the File ---
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            
            // --- 4. Update the Database ---
            // The path to store in the DB should be from the root
            $db_path = 'uploads/' . $new_filename; 
            
            $stmt = $conn->prepare("UPDATE users SET profile_picture_url = ? WHERE id = ?");
            $stmt->bind_param("si", $db_path, $user_id);
            
            if ($stmt->execute()) {
                // --- 5. Success! Update Session & Send Response ---
                $_SESSION['profile_picture_url'] = $db_path; // Update session
                
                $response['success'] = true;
                $response['message'] = 'Profile picture updated!';
                $response['new_path'] = $db_path;
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
            
        } else {
            $response['message'] = 'Failed to save the uploaded file.';
        }
    }
} else {
    $response['message'] = 'No file was uploaded or an error occurred.';
}

$conn->close();
echo json_encode($response);
?>
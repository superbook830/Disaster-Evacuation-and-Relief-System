<?php
// Include the new session manager
include_once 'api/config/session.php';

// "No Loophole" Smart Bouncer:
if (isset($_SESSION['user_id'])) {
    
    // Check their role
    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'resident') {
        header('Location: my_dashboard.php');
        exit;
    }
    
    // Fallback for any other role
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;

} else {
    // If no one is logged in, send them to login.
    header('Location: login.php');
    exit;
}
?>
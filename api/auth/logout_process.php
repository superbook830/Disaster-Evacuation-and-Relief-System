<?php
// Include the new session manager
include_once '../config/session.php';

// Unset all of the session variables.
session_unset();

// Finally, destroy the session.
session_destroy();

// Send the user back to the login page.
header("Location: ../../login.php");
exit;
?>
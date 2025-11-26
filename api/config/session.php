<?php
// "No Loophole" Brute-Force Session Fix for Localhost
session_set_cookie_params([
    'path' => '/', // Set path to the root of the domain
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Now, start the session
session_start();
?>
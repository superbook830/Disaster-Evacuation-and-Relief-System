<?php
// --- ONE-TIME USE SCRIPT ---
// Delete this file after you run it!

// Set the password you want to use
$my_password = 'admin123'; // <-- CHANGE THIS to your desired password

// Generate a secure hash
$hash = password_hash($my_password, PASSWORD_DEFAULT);

// Display the hash
echo "Your new password is: <strong>" . $my_password . "</strong><br>";
echo "Copy this hash into phpMyAdmin:<br><br>";
echo "<strong>" . $hash . "</strong>";

?>
<?php
// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set your database credentials
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty. Change this if you set one.
$dbname = "disaster_db"; // You named this!

// Create the database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    // If connection fails, stop the script and show the error.
    // This is a "no loophole" step. We don't want the site to run
    // if it can't talk to the database.
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to utf8mb4 for full language support
$conn->set_charset("utf8mb4");

// We don't need to close the connection here. 
// PHP will do it automatically at the end of each script 
// that includes this file.
?>
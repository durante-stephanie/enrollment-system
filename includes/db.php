<?php
// Report all PHP errors and throw exceptions for MySQLi errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection variables
$host = "localhost";
$user = "root";
$pass = "";
$db = "dbenrollment";
$port = 3306;

// Create a new mysqli connection object
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check for connection errors
if ($conn->connect_error) {
  // Stop the script and display a detailed error message
  die("Connection failed: " . $conn->connect_error);
}
?>
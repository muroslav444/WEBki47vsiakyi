<?php
// Database connection data
$servername = 'localhost';
$username = 'username';
$password = 'password';
$dbname = 'databasename';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die('Connection failed: ' . $conn->connect_error);
}

// Checking if users table exists; if not, create it
$sql_users = "CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(255) NOT NULL,
password VARCHAR(255) NOT NULL
)";

if ($conn->query($sql_users) !== TRUE) {
  echo "Error creating users table: " . $conn->error;
}

// Checking if user_logins table exists; if not, create it
$sql_user_logins = "CREATE TABLE IF NOT EXISTS user_logins (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(255) NOT NULL,
ip_address VARCHAR(45) NOT NULL,
is_logged_out BOOLEAN DEFAULT 0
)";

// If an error occurred with user_logins table creating, report it
if ($conn->query($sql_user_logins) !== TRUE) {
  echo "Error creating user_logins table: " . $conn->error;
}
?>

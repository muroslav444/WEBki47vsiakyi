<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

include 'db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'];
$password = $data['password'];

if (empty($username) || empty($password)) {
  echo json_encode(['success' => false]);
  exit;
}

// Check if the username already exists
$checkUsernameQuery = "SELECT * FROM users WHERE username = '$username'";
$checkUsernameResult = mysqli_query($conn, $checkUsernameQuery);

if (mysqli_num_rows($checkUsernameResult) > 0) {
  // Username already exists
  echo json_encode(['success' => false, 'message' => 'Username already exists']);
  exit;
}

// Use password_hash for secure password hashing
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashedPassword')";

$result = mysqli_query($conn, $sql);

if ($result) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => 'Registration failed']);
}

mysqli_close($conn);
?>

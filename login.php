<?php
// Set headers for JSON response
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

// Include necessary files
include 'db_connection.php'; // Database connection
require 'vendor/autoload.php'; // Required libraries

// Function to get the username if the user is logged in
function getUsername($conn, $ipAddress) {
    $loggedInSql = "SELECT * FROM user_logins WHERE ip_address = '$ipAddress' AND is_logged_out = 0";
    $loggedInResult = mysqli_query($conn, $loggedInSql);

    if ($loggedInResult && mysqli_num_rows($loggedInResult) > 0) {
        $row = mysqli_fetch_assoc($loggedInResult);
        $username = $row['username'];
        return $username;
    }

    return null;
}

// Function to handle regular login
function handleRegularLogin($conn, $username, $password, $ipAddress) {
    // Check if username or password is empty
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit;
    }

    // Check if the user is already logged in from any account with this IP
    $loginCheckSql = "SELECT * FROM user_logins WHERE ip_address = '$ipAddress' AND is_logged_out = 0";
    $loginCheckResult = mysqli_query($conn, $loginCheckSql);

    if (mysqli_num_rows($loginCheckResult) > 0) {
        echo json_encode(['success' => false, 'message' => 'You are already logged in. Log out to log in again.']);
        exit;
    }

    // Proceed with regular login check
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $hashedPassword = $row['password'];

            if (password_verify($password, $hashedPassword)) {
                // Log successful login and store IP address
                $insertLoginSql = "INSERT INTO user_logins (username, ip_address, is_logged_out) VALUES ('$username', '$ipAddress', 0)";
                mysqli_query($conn, $insertLoginSql);

                echo json_encode(['success' => true, 'username' => $username]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User does not exist']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'An error occurred', 'sql_error' => mysqli_error($conn)]);
    }
}

// Check if it's a login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];
    $password = $data['password'];
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    // Handle login attempt
    handleRegularLogin($conn, $username, $password, $ipAddress);
} else {
    // Handle cases where user is already logged in
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $loggedInUsername = getUsername($conn, $ipAddress);

    if ($loggedInUsername) {
        echo json_encode(['success' => true, 'username' => $loggedInUsername]);
    }
}

// Close database connection
mysqli_close($conn);
?>

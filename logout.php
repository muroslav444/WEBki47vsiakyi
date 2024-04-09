<?php
// Include the database connection file
include 'db_connection.php';

// Function to log out the user and remove the entry from user_logins table
function handleLogout($conn, $ipAddress) {

    // Remove the entry from user_logins table
    $deleteLoginSql = "DELETE FROM user_logins WHERE ip_address = '$ipAddress'";
    mysqli_query($conn, $deleteLoginSql);

    // Send a JSON response indicating successful logout
    echo json_encode(['success' => true]);
}

// Check if it's a logout request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get the user's IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    
    // Call the handleLogout function with the database connection and IP address
    handleLogout($conn, $ipAddress);
} else {
    // Send a JSON response for an invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close the database connection
mysqli_close($conn);
?>

<?php
// Include the required autoload file for Composer and the database connection file
require 'google_config.php';
include 'db_connection.php';

// Function to handle MySQL operations after successful Google login
function processGoogleLogin($conn, $username) {
    try {
        // Get the user's IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        // Insert a new login record into the user_logins table
        $insertLoginSql = "INSERT INTO user_logins (username, ip_address, is_logged_out) VALUES ('$username', '$ipAddress', 0)";
        $result = mysqli_query($conn, $insertLoginSql);

        if ($result) {
            // Redirect to index.html after successful login
            header('Location: index');
            exit;
        } else {
            // Return a JSON response if there's an error inserting login data
            echo json_encode(['success' => false, 'message' => 'Error inserting login data']);
            exit;
        }
    } catch (Exception $e) {
        // Log other exceptions during MySQL operations
        error_log("Exception during MySQL operations: " . $e->getMessage());

        // Return a JSON response for unexpected errors
        echo json_encode(['success' => false, 'message' => 'An unexpected error occurred. Please try again later.']);
        exit;
    }
}

// Check if the Google login was successful and username is available
if (isset($_GET['code'])) {
    // Set up the Google client with credentials and scopes
    $client = new Google_Client();
    $client->setClientId('ClientId');
    $client->setClientSecret('ClientSecret');
    $client->setRedirectUri('RedirectUri');
    $client->addScope("email");
    $client->addScope("profile");

    // Exchange the authorization code for an access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token["error"])) {
        // Set the access token and retrieve user information
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        // Escape the email address to prevent SQL injection
        $email = mysqli_real_escape_string($conn, $google_account_info->email);

        // Query the database to check if the user already exists
        $get_user = mysqli_query($conn, "SELECT `username` FROM `users` WHERE `email`='$email'");

        if ($get_user && mysqli_num_rows($get_user) > 0) {
            // If user exists, retrieve the username and call the function to handle login
            $row = mysqli_fetch_assoc($get_user);
            $username = $row['username'];
            processGoogleLogin($conn, $username);
        } else {
            // If user does not exist, create a new user using given name as the username
            $newUsername = $google_account_info->givenName;

            // Check if the new user already exists
            $checkUserSql = "SELECT `username` FROM `users` WHERE `username`='$newUsername'";
            $checkUserResult = mysqli_query($conn, $checkUserSql);

            if ($checkUserResult && mysqli_num_rows($checkUserResult) === 0) {
                // If the new user does not exist, insert into the users table and handle login
                $insertUserSql = "INSERT INTO users (username) VALUES ('$newUsername')";
                $result = mysqli_query($conn, $insertUserSql);

                if ($result) {
                    processGoogleLogin($conn, $newUsername);
                } else {
                    // Return a JSON response if there's an error creating a new user
                    echo json_encode(['success' => false, 'message' => 'Error creating new user']);
                    exit;
                }
            } else {
                // If user already exists, proceed with login
                processGoogleLogin($conn, $newUsername);
            }
        }
    } else {
        // Log the error from Google authentication
        error_log("Google authentication error: " . json_encode($token["error"]));

        // Return a JSON response for Google authentication errors
        echo json_encode(['success' => false, 'message' => 'Error during Google authentication: ' . json_encode($token["error"])]);
        exit;
    }
} else {
    // Return a JSON response for invalid requests
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}
?>

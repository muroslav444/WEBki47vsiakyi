<?php
// Connection of libraries
require 'google_config.php';

// Create a Google authentication URL and send it to the frontend
$authUrl = $client->createAuthUrl();
echo json_encode(['success' => true, 'authUrl' => $authUrl]);
exit;
?>

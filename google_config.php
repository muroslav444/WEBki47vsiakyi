// Connection of libraries
require 'vendor/autoload.php';

// Initialize the Google client
$client = new Google_Client();
$client->setClientId('ClientId');
$client->setClientSecret('ClientSecret');
$client->setRedirectUri('RedirectUri');
$client->addScope("email");
$client->addScope("profile");
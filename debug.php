<?php
// Just a simple debug file

session_start();

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Parse URL manually
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

echo "<pre>";
echo "URL: " . print_r($url, true) . "\n";

// Test AdminController
echo "\nTesting AdminController:\n";
$adminController = new App\Controllers\AdminController();

// Get class methods
$methods = get_class_methods($adminController);

echo "\nAvailable methods:\n";
print_r($methods);

// Check if specific methods exist
echo "\nSpecific Method Checks:\n";
echo "add_campaign exists: " . (method_exists($adminController, 'add_campaign') ? 'Yes' : 'No') . "\n";
echo "addCampaign exists: " . (method_exists($adminController, 'addCampaign') ? 'Yes' : 'No') . "\n";

echo "</pre>"; 
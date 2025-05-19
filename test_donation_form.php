<?php
// Test file to debug donation form loading

// Load autoloader from Composer
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Get the campaign slug from URL parameter
$slug = $_GET['slug'] ?? 'aku-kaya-1747482070';

echo "<h1>Testing Donation Form Loading</h1>";
echo "<p>Trying to load donation form for campaign with slug: {$slug}</p>";

try {
    // Create instances of necessary models and controllers
    $campaignModel = new App\Models\Campaign();
    $donationController = new App\Controllers\DonationController();
    
    // Try to find the campaign by slug
    $campaign = $campaignModel->findBySlug($slug);
    
    if ($campaign) {
        echo "<h2>Campaign Found!</h2>";
        echo "<p>Campaign ID: {$campaign['id']}</p>";
        echo "<p>Campaign Title: {$campaign['title']}</p>";
        
        echo "<h3>Donation Form Link</h3>";
        echo "<p><a href='".BASE_URL."/donation/form/{$slug}'>Click here to go to donation form</a></p>";
        
        echo "<pre>";
        print_r($campaign);
        echo "</pre>";
    } else {
        echo "<h2>Campaign Not Found!</h2>";
        echo "<p>No campaign found with slug: {$slug}</p>";
    }
} catch (Exception $e) {
    echo "<h2>Error Occurred</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<pre>";
    print_r($e->getTrace());
    echo "</pre>";
} 
<?php
// Test file to debug campaign loading

// Load autoloader from Composer
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Get the campaign slug from URL parameter
$slug = $_GET['slug'] ?? 'aku-kaya-1747482070';

echo "<h1>Testing Campaign Loading</h1>";
echo "<p>Trying to load campaign with slug: {$slug}</p>";

try {
    // Create an instance of the Campaign model
    $campaignModel = new App\Models\Campaign();
    
    // Try to find the campaign by slug
    $campaign = $campaignModel->findBySlug($slug);
    
    if ($campaign) {
        echo "<h2>Campaign Found!</h2>";
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
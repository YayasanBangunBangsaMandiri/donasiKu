<?php
/**
 * Direct test for DonationController
 * This bypasses the routing system
 */

// Turn on error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load configurations
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Direct Test for DonationController</h1>";

// Campaign slug to test
$slug = 'aku-kaya-1747482070';

try {
    // Create a campaign model and get the campaign data
    $campaignModel = new App\Models\Campaign();
    $campaign = $campaignModel->findBySlug($slug);
    
    echo "<h2>Campaign Data:</h2>";
    echo "<pre>";
    print_r($campaign);
    echo "</pre>";
    
    if (!$campaign) {
        echo "<p style='color: red;'>Campaign not found with slug: {$slug}</p>";
        exit;
    }
    
    // Create donation amounts
    $donationAmounts = DEFAULT_DONATION_AMOUNTS;
    if (!empty($campaign['donation_amounts'])) {
        $donationAmounts = json_decode($campaign['donation_amounts'], true);
    }
    
    echo "<h2>Donation Amounts:</h2>";
    echo "<pre>";
    print_r($donationAmounts);
    echo "</pre>";
    
    // Create the donation form HTML manually
    echo "<h2>Donation Form:</h2>";
    echo "<form action='" . BASE_URL . "/donation/process' method='post'>";
    echo "<input type='hidden' name='campaign_id' value='" . $campaign['id'] . "'>";
    echo "<input type='hidden' name='campaign_slug' value='" . $campaign['slug'] . "'>";
    
    echo "<div>";
    echo "<label>Donation Amount:</label><br>";
    foreach ($donationAmounts as $amount => $formatted) {
        echo "<label><input type='radio' name='amount' value='{$amount}'> Rp {$formatted}</label><br>";
    }
    echo "</div>";
    
    echo "<div style='margin-top: 20px;'>";
    echo "<label>Your Name:</label><br>";
    echo "<input type='text' name='name' required>";
    echo "</div>";
    
    echo "<div style='margin-top: 10px;'>";
    echo "<label>Your Email:</label><br>";
    echo "<input type='email' name='email' required>";
    echo "</div>";
    
    echo "<div style='margin-top: 10px;'>";
    echo "<label>Your Phone:</label><br>";
    echo "<input type='tel' name='phone'>";
    echo "</div>";
    
    echo "<div style='margin-top: 10px;'>";
    echo "<label>Message (Optional):</label><br>";
    echo "<textarea name='message'></textarea>";
    echo "</div>";
    
    echo "<div style='margin-top: 10px;'>";
    echo "<label><input type='checkbox' name='is_anonymous' value='1'> Make donation anonymous</label>";
    echo "</div>";
    
    echo "<div style='margin-top: 10px;'>";
    echo "<label>Payment Method:</label><br>";
    echo "<label><input type='radio' name='payment_method' value='bank_transfer' checked> Bank Transfer</label><br>";
    echo "<label><input type='radio' name='payment_method' value='e-wallet'> E-Wallet</label>";
    echo "</div>";
    
    echo "<div style='margin-top: 20px;'>";
    echo "<button type='submit' style='padding: 10px 20px; background-color: #0d6efd; color: white; border: none; cursor: pointer;'>Make Donation</button>";
    echo "</div>";
    
    echo "</form>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error:</h2>";
    echo "<p>{$e->getMessage()}</p>";
    echo "<pre>";
    print_r($e->getTrace());
    echo "</pre>";
} 
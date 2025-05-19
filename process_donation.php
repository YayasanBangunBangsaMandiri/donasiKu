<?php
/**
 * Direct donation processing script
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

// Start session
session_start();

try {
    // Create models and helpers
    $donationModel = new App\Models\Donation();
    $campaignModel = new App\Models\Campaign();
    $dokuHelper = new App\Helpers\DokuHelper();
    $db = Database::getInstance();
    
    // Validate input
    $errors = [];
    
    // Check if fields are present
    if (empty($_POST['campaign_id'])) {
        $errors['campaign_id'] = ['Campaign ID is required'];
    }
    
    if (empty($_POST['name'])) {
        $errors['name'] = ['Name is required'];
    } elseif (strlen($_POST['name']) > 100) {
        $errors['name'] = ['Name must be less than 100 characters'];
    }
    
    if (empty($_POST['email'])) {
        $errors['email'] = ['Email is required'];
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = ['Invalid email format'];
    }
    
    if (empty($_POST['amount'])) {
        $errors['amount'] = ['Amount is required'];
    } elseif (!is_numeric($_POST['amount'])) {
        $errors['amount'] = ['Amount must be a number'];
    } elseif ($_POST['amount'] < MIN_DONATION_AMOUNT) {
        $errors['amount'] = ['Amount must be at least Rp ' . number_format(MIN_DONATION_AMOUNT, 0, ',', '.')];
    } elseif ($_POST['amount'] > MAX_DONATION_AMOUNT) {
        $errors['amount'] = ['Amount must be at most Rp ' . number_format(MAX_DONATION_AMOUNT, 0, ',', '.')];
    }
    
    if (empty($_POST['payment_method'])) {
        $errors['payment_method'] = ['Payment method is required'];
    }
    
    // Menangani validasi phone jika ada
    if (!empty($_POST['phone'])) {
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            $errors['phone'] = ['Phone number must be between 10-15 digits'];
        }
    }
    
    // If there are validation errors, redirect back to the form
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header('Location: ' . BASE_URL . '/donate.php?campaign=' . $_POST['campaign_slug']);
        exit;
    }
    
    // Get campaign data
    $campaign = $campaignModel->find($_POST['campaign_id']);
    
    if (!$campaign) {
        $_SESSION['error'] = 'Campaign not found';
        header('Location: ' . BASE_URL);
        exit;
    }
    
    // Check if campaign is active
    if ($campaign['status'] !== 'active') {
        $statusMessage = 'This campaign is not active';
        if ($campaign['status'] === 'completed') {
            $statusMessage = 'This campaign has already reached its goal amount. Thank you for your interest!';
        }
        $_SESSION['error'] = $statusMessage;
        header('Location: ' . BASE_URL);
        exit;
    }
    
    // Check if campaign has ended
    $endDate = new DateTime($campaign['end_date']);
    $today = new DateTime();
    
    if ($today > $endDate) {
        $_SESSION['error'] = 'This campaign has ended';
        header('Location: ' . BASE_URL);
        exit;
    }
    
    // Check if campaign has reached its goal amount
    if ($campaign['current_amount'] >= $campaign['goal_amount']) {
        // Update campaign status to completed
        $db->query(
            "UPDATE campaigns SET status = 'completed' WHERE id = ?",
            [$campaign['id']]
        );
        
        $_SESSION['error'] = 'This campaign has already reached its goal amount. Thank you for your interest!';
        header('Location: ' . BASE_URL . '/campaign/detail/' . $campaign['slug']);
        exit;
    }
    
    // Check if this donation would exceed the goal amount
    $newAmount = $campaign['current_amount'] + $_POST['amount'];
    $willReachGoal = false;
    
    if ($newAmount >= $campaign['goal_amount']) {
        // Allow the donation, but flag that it will complete the campaign
        $willReachGoal = true;
    }
    
    // Prepare donation data
    $donationData = [
        'campaign_id' => $_POST['campaign_id'],
        'user_id' => isset($_SESSION['user']) ? $_SESSION['user']['id'] : null,
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'] ?? '',
        'amount' => $_POST['amount'],
        'payment_method' => $_POST['payment_method'],
        'message' => $_POST['message'] ?? '',
        'is_anonymous' => isset($_POST['is_anonymous']) ? 1 : 0,
        'status' => 'pending',
        'order_id' => 'ORD-' . time() . '-' . rand(1000, 9999), // Generate order ID
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Save donation to database
    $donationId = $donationModel->create($donationData);
    
    if (!$donationId) {
        $_SESSION['error'] = 'Failed to create donation. Please try again.';
        header('Location: ' . BASE_URL . '/donate.php?campaign=' . $campaign['slug']);
        exit;
    }
    
    // Get full donation data
    $donation = $donationModel->find($donationId);
    
    // Create Doku transaction
    $transaction = $dokuHelper->createTransaction($donation, $campaign);
    
    if (!$transaction['success']) {
        // Log the error details
        error_log('Payment transaction failed: ' . ($transaction['message'] ?? 'Unknown error'));
        
        // Set a more user-friendly error message
        $errorMessage = 'Payment gateway error. ';
        
        if (isset($transaction['message'])) {
            // Use the error message but remove technical details
            $cleanMessage = $transaction['message'];
            
            // Remove any sensitive information
            $cleanMessage = preg_replace('/Client-Id:.*?\\n/i', '', $cleanMessage);
            $cleanMessage = preg_replace('/Secret-Key:.*?\\n/i', '', $cleanMessage);
            $cleanMessage = preg_replace('/API key:.*?\\n/i', '', $cleanMessage);
            
            // For user display, simplify the error message
            if (strpos($cleanMessage, 'timeout') !== false) {
                $errorMessage .= 'Payment service is temporarily unavailable. Please try again later.';
            } elseif (strpos($cleanMessage, 'connect') !== false) {
                $errorMessage .= 'Cannot connect to payment service. Please try again later.';
            } else {
                $errorMessage .= 'Please try again or choose another payment method.';
            }
        } else {
            $errorMessage .= 'Please try again later or contact support.';
        }
        
        $_SESSION['error'] = $errorMessage;
        header('Location: ' . BASE_URL . '/donate.php?campaign=' . $campaign['slug']);
        exit;
    }
    
    // Set a flag to mark the campaign as completed when this payment succeeds
    if ($willReachGoal) {
        $db->query(
            "UPDATE donations SET complete_campaign = 1 WHERE id = ?",
            [$donationId]
        );
    }
    
    // Redirect to Doku payment page
    header('Location: ' . $transaction['redirect_url']);
    exit;
    
} catch (Exception $e) {
    // Log the error
    error_log('Donation Processing Error: ' . $e->getMessage());
    
    // Show an error page
    require_once __DIR__ . '/app/Views/partials/header.php';
    echo '<div class="container py-5">';
    echo '<div class="alert alert-danger">';
    echo '<h3>Error Processing Donation</h3>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<p><a href="' . BASE_URL . '">Go back to home</a></p>';
    echo '</div>';
    echo '</div>';
    require_once __DIR__ . '/app/Views/partials/footer.php';
} 
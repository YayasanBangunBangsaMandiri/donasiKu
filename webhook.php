<?php
/**
 * Doku Webhook Handler
 * Receives payment notifications from Doku
 */

// Turn off error display in production
ini_set('display_errors', 0);
error_reporting(0);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/webhook_errors.log');

// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load configurations
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

/**
 * Verify Doku notification signature
 * 
 * @param array $headers Request headers
 * @param string $body Request body
 * @return bool Whether signature is valid
 */
function verifyDokuSignature($headers, $body) {
    // Log headers and body for debugging
    error_log('Webhook Headers: ' . json_encode($headers));
    error_log('Webhook Body: ' . json_encode($body));
    
    // Verify signature only if we're in production mode
    if (DEBUG_MODE) {
        error_log('DEBUG_MODE is enabled - skipping signature verification');
        return true;
    }
    
    // Check if required headers are present
    if (!isset($headers['Signature']) || !isset($headers['Request-Id']) || !isset($headers['Client-Id'])) {
        error_log('Missing required Doku headers');
        return false;
    }
    
    try {
        // Get signature from headers
        $signature = $headers['Signature'];
        
        // Remove HMACSHA256= prefix if present
        if (strpos($signature, 'HMACSHA256=') === 0) {
            $signature = substr($signature, 11);
        }
        
        // Decode signature
        $decodedSignature = base64_decode($signature);
        
        // Get Doku public key
        $publicKey = DOKU_PUBLIC_KEY;
        
        // Load public key
        $publicKeyResource = openssl_pkey_get_public($publicKey);
        if (!$publicKeyResource) {
            error_log('Failed to load Doku public key: ' . openssl_error_string());
            return false;
        }
        
        // Verify signature
        $verified = openssl_verify($body, $decodedSignature, $publicKeyResource, OPENSSL_ALGO_SHA256);
        openssl_free_key($publicKeyResource);
        
        if ($verified === 1) {
            error_log('Doku signature verification successful');
            return true;
        } else if ($verified === 0) {
            error_log('Doku signature verification failed');
            return false;
        } else {
            error_log('Doku signature verification error: ' . openssl_error_string());
            return false;
        }
    } catch (Exception $e) {
        error_log('Doku signature verification exception: ' . $e->getMessage());
        return false;
    }
}

// Get request headers
$headers = getallheaders();

// Get request body
$body = file_get_contents('php://input');
$notification = json_decode($body, true);

// Log the notification
error_log('Doku Webhook Notification Received: ' . json_encode($notification));

// Verify signature (in real implementation)
$validSignature = verifyDokuSignature($headers, $body);

if (!$validSignature) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    exit;
}

// Initialize Doku helper
$dokuHelper = new App\Helpers\DokuHelper();

// Process the notification
$result = $dokuHelper->processNotification($notification);

if ($result['success']) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Notification processed successfully']);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
} 
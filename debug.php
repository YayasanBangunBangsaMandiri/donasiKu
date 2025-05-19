<?php
/**
 * Debug and Test Tool for Doku Integration
 */

// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load configurations
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Initialize DB
$db = Database::getInstance();

// Enable full error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Initialize Doku helper
$dokuHelper = new App\Helpers\DokuHelper();

// Set title and heading based on action
$title = 'Doku Integration Debug Tool';
$heading = 'Doku Integration Debug Tool';

// Check if a specific action is requested
$action = $_GET['action'] ?? 'info';

// Process actions
$result = '';
$message = '';

// Handle toggling DEBUG_MODE if requested
if (isset($_GET['toggle_debug'])) {
    $newValue = $_GET['toggle_debug'] === '1' ? 'true' : 'false';
    
    // Read current config file
    $configFile = file_get_contents(__DIR__ . '/config/config.php');
    
    // Replace DEBUG_MODE value
    $configFile = preg_replace(
        '/define\(\'DEBUG_MODE\',\s*(true|false)\)/',
        "define('DEBUG_MODE', $newValue)",
        $configFile
    );
    
    // Write updated config back
    file_put_contents(__DIR__ . '/config/config.php', $configFile);
    
    // Redirect to refresh with updated settings
    header('Location: ' . $_SERVER['PHP_SELF'] . '?action=info&debug_toggled=1');
    exit;
}

switch ($action) {
    case 'test_signature':
        $heading = 'Test Signature Generation';
        
        // Generate signature for testing
        $timestamp = gmdate("Y-m-d\TH:i:s\Z");
        $requestId = bin2hex(random_bytes(12));
        $requestTarget = "/checkout/v1/payment";
        
        $body = [
            'order' => [
                'amount' => 10000,
                'invoice_number' => 'TEST-' . time()
            ]
        ];
        
        $bodyJson = json_encode($body);
        
        try {
            // This uses a reflection class to access the private method
            $reflectionMethod = new ReflectionMethod('App\Helpers\DokuHelper', 'generateSignature');
            $reflectionMethod->setAccessible(true);
            
            $signature = $reflectionMethod->invoke($dokuHelper, $requestTarget, 'POST', $timestamp, $requestId, $bodyJson);
            
            $result = [
                'signature' => $signature,
                'request_id' => $requestId,
                'timestamp' => $timestamp,
                'request_target' => $requestTarget,
                'body' => $body
            ];
            
            $message = 'Signature generated successfully';
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'test_create_transaction':
        $heading = 'Test Create Transaction';
        
        // Create a test transaction
        $donationId = $_GET['donation_id'] ?? null;
        
        if (!$donationId) {
            $message = 'Error: Donation ID is required';
            break;
        }
        
        try {
            // Get donation and campaign
            $donation = $db->fetch("SELECT * FROM donations WHERE id = ?", [$donationId]);
            
            if (!$donation) {
                $message = 'Error: Donation not found';
                break;
            }
            
            $campaign = $db->fetch("SELECT * FROM campaigns WHERE id = ?", [$donation['campaign_id']]);
            
            if (!$campaign) {
                $message = 'Error: Campaign not found';
                break;
            }
            
            // Create transaction
            $response = $dokuHelper->createTransaction($donation, $campaign);
            
            $result = $response;
            
            if ($response['success']) {
                $message = 'Transaction created successfully. Redirect URL: ' . $response['redirect_url'];
            } else {
                $message = 'Error: ' . $response['message'];
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'test_check_transaction':
        $heading = 'Test Check Transaction';
        
        // Check a transaction status
        $orderId = $_GET['order_id'] ?? null;
        
        if (!$orderId) {
            $message = 'Error: Order ID is required';
            break;
        }
        
        try {
            $response = $dokuHelper->checkTransaction($orderId);
            
            $result = $response;
            
            if ($response['success']) {
                $message = 'Transaction status: ' . $response['status'];
            } else {
                $message = 'Error: ' . $response['message'];
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
        }
        break;
        
    default:
        // Show Doku configuration info
        $heading = 'Doku Configuration Information';
        
        $result = [
            'client_id' => DOKU_CLIENT_ID,
            'merchant_id' => DOKU_MERCHANT_ID,
            'environment' => DOKU_ENVIRONMENT,
            'base_url' => $dokuHelper->baseUrl ?? (DOKU_ENVIRONMENT === 'production' ? 'https://api.doku.com/' : 'https://api-sandbox.doku.com/'),
            'debug_mode' => DEBUG_MODE ? 'Enabled' : 'Disabled',
            'webhook_url' => BASE_URL . '/webhook.php',
            'return_url' => BASE_URL . '/donation_success.php',
            'public_key_configured' => !empty(DOKU_PUBLIC_KEY) ? 'Yes' : 'No'
        ];
        
        $message = isset($_GET['debug_toggled']) ? 'Debug mode setting updated' : 'Configuration loaded successfully';
        break;
}

// HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
        .nav-pills .nav-link.active { background-color: #0d6efd; }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4"><?php echo $heading; ?></h1>
        
        <div class="row mb-4">
            <div class="col">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action === 'info' ? 'active' : ''; ?>" href="?action=info">Configuration Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action === 'test_signature' ? 'active' : ''; ?>" href="?action=test_signature">Test Signature</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action === 'test_create_transaction' ? 'active' : ''; ?>" href="?action=test_create_transaction">Test Create Transaction</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action === 'test_check_transaction' ? 'active' : ''; ?>" href="?action=test_check_transaction">Test Check Transaction</a>
                    </li>
                </ul>
            </div>
        </div>
        
        <?php if (!empty($message)): ?>
        <div class="alert <?php echo strpos($message, 'Error') === 0 ? 'alert-danger' : 'alert-success'; ?> mb-4">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($action === 'test_create_transaction' && empty($_GET['donation_id'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Test Create Transaction</h5>
            </div>
            <div class="card-body">
                <form action="?" method="get">
                    <input type="hidden" name="action" value="test_create_transaction">
                    <div class="mb-3">
                        <label for="donation_id" class="form-label">Donation ID</label>
                        <input type="number" class="form-control" id="donation_id" name="donation_id" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Test Create Transaction</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($action === 'test_check_transaction' && empty($_GET['order_id'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Test Check Transaction</h5>
            </div>
            <div class="card-body">
                <form action="?" method="get">
                    <input type="hidden" name="action" value="test_check_transaction">
                    <div class="mb-3">
                        <label for="order_id" class="form-label">Order ID</label>
                        <input type="text" class="form-control" id="order_id" name="order_id" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Check Transaction</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($result)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Result</h5>
            </div>
            <div class="card-body">
                <pre><?php echo json_encode($result, JSON_PRETTY_PRINT); ?></pre>
                
                <?php if ($action === 'test_create_transaction' && !empty($result['redirect_url'])): ?>
                <div class="mt-3">
                    <a href="<?php echo $result['redirect_url']; ?>" class="btn btn-success" target="_blank">Go to Payment Page</a>
                </div>
                <?php endif; ?>
                
                <?php if ($action === 'info'): ?>
                <div class="mt-3">
                    <hr>
                    <h5>Debug Mode Setting</h5>
                    <p>
                        Debug Mode is currently: <strong><?php echo DEBUG_MODE ? 'Enabled' : 'Disabled'; ?></strong>
                    </p>
                    <p>
                        <small class="text-muted">
                            When Debug Mode is enabled, the system will fall back to the payment simulator if Doku API calls fail.
                            When disabled, only real Doku payment gateway will be used.
                        </small>
                    </p>
                    <a href="?toggle_debug=<?php echo DEBUG_MODE ? '0' : '1'; ?>" class="btn btn-<?php echo DEBUG_MODE ? 'warning' : 'primary'; ?>">
                        <?php echo DEBUG_MODE ? 'Disable' : 'Enable'; ?> Debug Mode
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

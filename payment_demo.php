<?php
/**
 * Doku Payment Simulator - Simplified Version
 * This simulates the Doku payment page
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

// Get donation ID and other parameters from URL
$donationId = $_GET['id'] ?? null;
$orderId = $_GET['order_id'] ?? null;

try {
    if (!$donationId) {
        throw new Exception('Donation ID is missing');
    }
    
    // Create models
    $donationModel = new App\Models\Donation();
    $campaignModel = new App\Models\Campaign();
    $db = Database::getInstance();
    
    // Get donation data
    $donation = $donationModel->find($donationId);
    
    if (!$donation) {
        throw new Exception('Donation not found');
    }
    
    // Get campaign data
    $campaign = $campaignModel->find($donation['campaign_id']);
    
    if (!$campaign) {
        throw new Exception('Campaign not found');
    }
    
    // Process form submission
    if (isset($_POST['submit_payment'])) {
        // Simulate notification to webhook
        $webhookData = [
            'order' => [
                'invoice_number' => $donation['order_id'],
                'amount' => $donation['amount']
            ],
            'transaction' => [
                'status' => 'SUCCESS',
                'payment_type' => $_POST['payment_channel'] ?? 'bank_transfer',
                'payment_date' => date('Y-m-d H:i:s')
            ],
            'customer' => [
                'name' => $donation['name'],
                'email' => $donation['email']
            ]
        ];
        
        // Process notification directly instead of calling webhook
        $dokuHelper = new App\Helpers\DokuHelper();
        $result = $dokuHelper->processNotification($webhookData);
        
        // Redirect to success page
        header('Location: ' . BASE_URL . '/donation_success.php?id=' . $donationId);
        exit;
    }
    
    // Get payment channels based on payment method
    $paymentChannels = [];
    if ($donation['payment_method'] == 'bank_transfer') {
        $paymentChannels = [
            'bca_va' => 'BCA Virtual Account',
            'mandiri_va' => 'Mandiri Virtual Account',
            'bri_va' => 'BRI Virtual Account',
            'bni_va' => 'BNI Virtual Account'
        ];
    } else if ($donation['payment_method'] == 'e-wallet') {
        $paymentChannels = [
            'gopay' => 'Gopay',
            'ovo' => 'OVO',
            'dana' => 'DANA',
            'shopeepay' => 'ShopeePay'
        ];
    } else {
        $paymentChannels = [
            'credit_card' => 'Credit Card',
            'bca_va' => 'BCA Virtual Account',
            'gopay' => 'Gopay',
            'qris' => 'QRIS'
        ];
    }
    
    // Include the header
    require_once __DIR__ . '/app/Views/partials/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Payment method selection -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Pilih Metode Pembayaran</h4>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info mb-4">
                        <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Halaman ini mensimulasikan proses pembayaran Doku.</p>
                    </div>
                    
                    <form method="post" action="" id="payment-form">
                        <div class="mb-4">
                            <h5 class="mb-3">Metode Pembayaran</h5>
                            <?php foreach ($paymentChannels as $channel => $name): ?>
                            <div class="form-check mb-3 border p-3 rounded">
                                <input class="form-check-input" type="radio" name="payment_channel" value="<?= $channel ?>" id="channel_<?= $channel ?>" <?= $channel === array_key_first($paymentChannels) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="channel_<?= $channel ?>">
                                    <?= $name ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h5>Instruksi Pembayaran</h5>
                            <p>Silakan pilih metode pembayaran dan klik tombol "Bayar Sekarang" untuk menyelesaikan donasi.</p>
                            <p>Nomor Pesanan: <strong><?= $donation['order_id'] ?></strong></p>
                            <p>Batas waktu pembayaran: <strong>1 jam</strong> dari sekarang</p>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" name="submit_payment" class="btn btn-primary btn-lg">
                                Bayar Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Order summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Ringkasan Donasi</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <p class="mb-1">Kampanye:</p>
                        <p class="fw-bold"><?= $campaign['title'] ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <p class="mb-1">Nomor Order:</p>
                        <p class="fw-bold"><?= $donation['order_id'] ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <p class="mb-1">Donatur:</p>
                        <p class="fw-bold"><?= $donation['name'] ?></p>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Jumlah Donasi</span>
                        <span>Rp <?= number_format($donation['amount'], 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total</span>
                        <span>Rp <?= number_format($donation['amount'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <p class="small text-muted">
                    <i class="fas fa-lock me-1"></i> Pembayaran aman & terenkripsi
                </p>
            </div>
        </div>
    </div>
</div>

<?php
    // Include the footer
    require_once __DIR__ . '/app/Views/partials/footer.php';
} catch (Exception $e) {
    // Show error page
    require_once __DIR__ . '/app/Views/partials/header.php';
    echo '<div class="container py-5">';
    echo '<div class="alert alert-danger">';
    echo '<h3>Error</h3>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<p><a href="' . BASE_URL . '">Go back to home</a></p>';
    echo '</div>';
    echo '</div>';
    require_once __DIR__ . '/app/Views/partials/footer.php';
} 
<?php
/**
 * Donation success page
 * This page is shown after a successful donation payment
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

// Get donation ID from URL parameter
$donationId = $_GET['id'] ?? null;

try {
    if (!$donationId) {
        throw new Exception('Donation ID is missing');
    }
    
    // Create models
    $donationModel = new App\Models\Donation();
    $campaignModel = new App\Models\Campaign();
    $dokuHelper = new App\Helpers\DokuHelper();
    
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
    
    // Check transaction status if needed
    $transaction = ['status' => 'success']; // Simplified for this example
    
    // Include the header
    require_once __DIR__ . '/app/Views/partials/header.php';
?>

<div class="container py-5">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
            </div>
            
            <h2 class="card-title mb-3">Terima Kasih Atas Donasi Anda!</h2>
            <p class="card-text mb-4">Donasi Anda sebesar <strong>Rp <?= number_format($donation['amount'], 0, ',', '.') ?></strong> untuk kampanye <strong><?= $campaign['title'] ?></strong> telah berhasil diproses.</p>
            
            <div class="mb-4">
                <div class="alert alert-info">
                    <h5>Detail Donasi:</h5>
                    <p class="mb-1"><strong>ID Donasi:</strong> <?= $donation['id'] ?></p>
                    <p class="mb-1"><strong>Nama:</strong> <?= $donation['name'] ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= $donation['email'] ?></p>
                    <p class="mb-1"><strong>Jumlah:</strong> Rp <?= number_format($donation['amount'], 0, ',', '.') ?></p>
                    <p class="mb-1"><strong>Metode Pembayaran:</strong> <?= ucfirst(str_replace('_', ' ', $donation['payment_method'])) ?></p>
                    <p class="mb-1"><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($donation['created_at'])) ?></p>
                    <p class="mb-0"><strong>Status:</strong> <?= ucfirst($donation['status']) ?></p>
                </div>
            </div>
            
            <div class="d-grid gap-2 col-md-6 mx-auto">
                <a href="<?= BASE_URL ?>/campaign/detail/<?= $campaign['slug'] ?>" class="btn btn-primary">Kembali ke Kampanye</a>
                <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary">Kembali ke Beranda</a>
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
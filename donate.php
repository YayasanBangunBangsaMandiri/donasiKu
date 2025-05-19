<?php
/**
 * Direct donation form page
 * This bypasses any routing issues
 */

// Turn on error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load configurations
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Get the campaign slug from URL parameter
$slug = $_GET['campaign'] ?? 'aku-kaya-1747482070';

try {
    // Create model and controller instances
    $campaignModel = new App\Models\Campaign();
    $db = Database::getInstance();
    
    // Get the campaign data
    $campaign = $campaignModel->findBySlug($slug);
    
    if (!$campaign) {
        echo "<div style='text-align: center; padding: 50px;'>";
        echo "<h1>Campaign Not Found</h1>";
        echo "<p>Sorry, the campaign you're looking for doesn't exist.</p>";
        echo "<p><a href='".BASE_URL."'>Go back to home</a></p>";
        echo "</div>";
        exit;
    }
    
    // Mendapatkan jumlah donasi preset
    $donationAmounts = DEFAULT_DONATION_AMOUNTS;
    if (!empty($campaign['donation_amounts'])) {
        $donationAmounts = json_decode($campaign['donation_amounts'], true);
    }
    
    // Mendapatkan panduan pembayaran
    $paymentGuides = $db->fetchAll(
        "SELECT * FROM payment_guides WHERE is_active = 1 ORDER BY payment_method, payment_channel"
    );
    
    // Include the header
    require_once __DIR__ . '/app/Views/partials/header.php';
?>

<div class="container py-5">
    <div class="row g-5">
        <!-- Detail Kampanye -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <img src="<?= BASE_URL ?>/public/uploads/<?= $campaign['featured_image'] ?>" class="card-img-top" alt="<?= $campaign['title'] ?>" style="height: 250px; object-fit: cover;">
                <div class="card-body">
                    <h4 class="card-title mb-3"><?= $campaign['title'] ?></h4>
                    <div class="progress mb-3" style="height: 10px;">
                        <?php $percentage = min(($campaign['current_amount'] / $campaign['goal_amount']) * 100, 100); ?>
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <p class="mb-0 fw-bold">Rp <?= number_format($campaign['current_amount'], 0, ',', '.') ?></p>
                            <small class="text-muted">terkumpul dari Rp <?= number_format($campaign['goal_amount'], 0, ',', '.') ?></small>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 fw-bold"><?= number_format($percentage, 0) ?>%</p>
                            <small class="text-muted">tercapai</small>
                        </div>
                    </div>
                    <p class="card-text"><?= $campaign['short_description'] ?></p>
                    <div class="d-flex justify-content-between text-muted small">
                        <span><i class="far fa-clock me-1"></i> <?= date('d M Y', strtotime($campaign['end_date'])) ?></span>
                        <span><i class="far fa-user me-1"></i> <?= $campaign['creator_name'] ?? 'Admin' ?></span>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Cara Donasi</h5>
                    <ol class="mb-0">
                        <li class="mb-2">Masukkan jumlah donasi dan data diri Anda</li>
                        <li class="mb-2">Pilih metode pembayaran yang Anda inginkan</li>
                        <li class="mb-2">Lakukan pembayaran sesuai instruksi</li>
                        <li class="mb-0">Donasi Anda akan terverifikasi secara otomatis</li>
                    </ol>
                </div>
            </div>
        </div>
        
        <!-- Form Donasi -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-lg-5">
                    <h4 class="card-title mb-4">Form Donasi</h4>
                    
                    <?php if (isset($_SESSION['errors'])): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['errors'] as $field => $errors): ?>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?= BASE_URL ?>/process_donation.php" method="post">
                        <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">
                        <input type="hidden" name="campaign_slug" value="<?= $campaign['slug'] ?>">
                        
                        <!-- Jumlah Donasi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Jumlah Donasi</label>
                            
                            <!-- Donasi Preset -->
                            <div class="row g-2 mb-3">
                                <?php foreach ($donationAmounts as $amount => $formatted): ?>
                                    <div class="col-6 col-md-3">
                                        <div class="form-check donation-amount-check">
                                            <input class="form-check-input" type="radio" name="donation_preset" id="donation_<?= $amount ?>" value="<?= $amount ?>" data-amount="<?= $amount ?>">
                                            <label class="form-check-label donation-amount w-100 text-center py-2" for="donation_<?= $amount ?>">
                                                Rp <?= $formatted ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Custom Amount -->
                            <?php $allowCustomAmount = !empty($campaign['allow_custom_amount']) ? $campaign['allow_custom_amount'] : ALLOW_CUSTOM_AMOUNT; ?>
                            <?php if ($allowCustomAmount): ?>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="amount" id="custom_amount" placeholder="Jumlah lainnya" min="<?= MIN_DONATION_AMOUNT ?>" max="<?= MAX_DONATION_AMOUNT ?>" value="<?= $_SESSION['old']['amount'] ?? '' ?>">
                                </div>
                                <div class="form-text">Minimal Rp <?= number_format(MIN_DONATION_AMOUNT, 0, ',', '.') ?> dan maksimal Rp <?= number_format(MAX_DONATION_AMOUNT, 0, ',', '.') ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Data Diri -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Data Diri</label>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="Masukkan nama lengkap" required value="<?= $_SESSION['old']['name'] ?? (isset($_SESSION['user']) ? $_SESSION['user']['name'] : '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="email" placeholder="Masukkan email" required value="<?= $_SESSION['old']['email'] ?? (isset($_SESSION['user']) ? $_SESSION['user']['email'] : '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Nomor HP</label>
                                <input type="tel" class="form-control" name="phone" id="phone" placeholder="Masukkan nomor HP" value="<?= $_SESSION['old']['phone'] ?? (isset($_SESSION['user']) ? $_SESSION['user']['phone'] : '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Pesan (Opsional)</label>
                                <textarea class="form-control" name="message" id="message" rows="3" placeholder="Masukkan pesan atau doa untuk kampanye ini"><?= $_SESSION['old']['message'] ?? '' ?></textarea>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_anonymous" id="is_anonymous" value="1" <?= isset($_SESSION['old']['is_anonymous']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_anonymous">
                                    Sembunyikan nama saya (donasi anonim)
                                </label>
                            </div>
                        </div>
                        
                        <!-- Metode Pembayaran -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Metode Pembayaran <span class="text-danger">*</span></label>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check payment-method-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_bank" value="bank_transfer" <?= ($_SESSION['old']['payment_method'] ?? '') == 'bank_transfer' ? 'checked' : '' ?>>
                                        <label class="form-check-label payment-method w-100 p-3" for="payment_bank">
                                            <i class="fas fa-university me-2"></i> Transfer Bank
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check payment-method-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_ewallet" value="e-wallet" <?= ($_SESSION['old']['payment_method'] ?? '') == 'e-wallet' ? 'checked' : '' ?>>
                                        <label class="form-check-label payment-method w-100 p-3" for="payment_ewallet">
                                            <i class="fas fa-wallet me-2"></i> E-Wallet
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">Lanjutkan Pembayaran</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script donasi -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle preset donation buttons
        const presetButtons = document.querySelectorAll('input[name="donation_preset"]');
        const customAmountInput = document.getElementById('custom_amount');
        
        if (presetButtons && customAmountInput) {
            presetButtons.forEach(function(button) {
                button.addEventListener('change', function() {
                    if (this.checked) {
                        customAmountInput.value = this.getAttribute('data-amount');
                    }
                });
            });
            
            customAmountInput.addEventListener('focus', function() {
                presetButtons.forEach(function(button) {
                    button.checked = false;
                });
            });
        }
    });
</script>

<?php
    // Include the footer
    require_once __DIR__ . '/app/Views/partials/footer.php';
} catch (Exception $e) {
    echo "<div style='text-align: center; padding: 50px;'>";
    echo "<h1>Error</h1>";
    echo "<p>{$e->getMessage()}</p>";
    echo "<p><a href='".BASE_URL."'>Go back to home</a></p>";
    echo "</div>";
} 
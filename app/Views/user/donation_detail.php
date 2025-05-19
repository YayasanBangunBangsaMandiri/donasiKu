<?php require_once BASEPATH . '/app/Views/partials/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php 
                        $profilePic = isset($_SESSION['user']['profile_picture']) 
                            ? $_SESSION['user']['profile_picture'] 
                            : 'default.jpg';
                        ?>
                        <img src="<?= BASE_URL ?>/public/uploads/<?= $profilePic ?>" alt="Profile" class="rounded-circle mb-3" width="100" height="100">
                        <h5 class="mb-1"><?= $_SESSION['user']['name'] ?></h5>
                        <p class="text-muted small"><?= $_SESSION['user']['email'] ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/user/dashboard" class="nav-link">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/user/profile" class="nav-link">
                                <i class="fas fa-user me-2"></i> Profil Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/user/donations" class="nav-link active">
                                <i class="fas fa-donate me-2"></i> Donasi Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/user/campaigns" class="nav-link">
                                <i class="fas fa-hand-holding-heart me-2"></i> Kampanye Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/auth/logout" class="nav-link text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Donasi</h5>
                        <a href="<?= BASE_URL ?>/user/donations" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4 mb-md-0">
                            <img src="<?= BASE_URL ?>/public/uploads/<?= $donation['campaign_image'] ?>" 
                                class="img-fluid rounded" alt="<?= $donation['campaign_title'] ?>">
                        </div>
                        <div class="col-md-8">
                            <h4 class="mb-3"><?= $donation['campaign_title'] ?></h4>
                            
                            <?php 
                            $statusClass = '';
                            $statusText = '';
                            
                            switch($donation['status']) {
                                case 'pending':
                                    $statusClass = 'warning';
                                    $statusText = 'Menunggu Pembayaran';
                                    break;
                                case 'success':
                                    $statusClass = 'success';
                                    $statusText = 'Pembayaran Berhasil';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'danger';
                                    $statusText = 'Pembayaran Dibatalkan';
                                    break;
                                default:
                                    $statusClass = 'secondary';
                                    $statusText = ucfirst($donation['status']);
                            }
                            ?>
                            
                            <div class="alert alert-<?= $statusClass ?> mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Status: <?= $statusText ?></h6>
                                        <?php if ($donation['status'] === 'pending'): ?>
                                            <p class="mb-0 small">Harap selesaikan pembayaran Anda</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">ID Donasi</div>
                                    <div class="col-md-8"><?= $donation['id'] ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Tanggal</div>
                                    <div class="col-md-8"><?= date('d F Y, H:i', strtotime($donation['created_at'])) ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Jumlah Donasi</div>
                                    <div class="col-md-8 fw-bold text-primary">Rp <?= number_format($donation['amount'], 0, ',', '.') ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Biaya Admin</div>
                                    <div class="col-md-8">Rp <?= number_format($donation['fee'] ?? 0, 0, ',', '.') ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Total</div>
                                    <div class="col-md-8 fw-bold">Rp <?= number_format(($donation['amount'] + ($donation['fee'] ?? 0)), 0, ',', '.') ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Metode Pembayaran</div>
                                    <div class="col-md-8"><?= $donation['payment_method'] ?? 'Midtrans' ?></div>
                                </div>
                                <?php if (!empty($donation['message'])): ?>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Pesan</div>
                                    <div class="col-md-8"><?= $donation['message'] ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($donation['status'] === 'pending'): ?>
                                <div class="d-grid gap-2">
                                    <a href="<?= BASE_URL ?>/payment/checkout/<?= $donation['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-credit-card me-2"></i> Lanjutkan Pembayaran
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($donation['status'] === 'success'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Terima Kasih Atas Donasi Anda!</h5>
                            <p class="mb-0 text-muted">Donasi Anda telah berhasil disalurkan ke kampanye ini.</p>
                        </div>
                    </div>
                    
                    <div class="alert alert-light border mb-0">
                        <p class="mb-0">Bantu menyebarkan kampanye ini ke media sosial untuk membantu lebih banyak orang:</p>
                        <div class="mt-3">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/campaign/detail/' . $donation['campaign_id']) ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fab fa-facebook-f me-1"></i> Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(BASE_URL . '/campaign/detail/' . $donation['campaign_id']) ?>&text=<?= urlencode('Saya baru saja berdonasi untuk ' . $donation['campaign_title']) ?>" 
                               target="_blank" class="btn btn-sm btn-outline-info me-2">
                                <i class="fab fa-twitter me-1"></i> Twitter
                            </a>
                            <a href="https://wa.me/?text=<?= urlencode('Saya baru saja berdonasi untuk ' . $donation['campaign_title'] . '. Ayo berdonasi juga! ' . BASE_URL . '/campaign/detail/' . $donation['campaign_id']) ?>" 
                               target="_blank" class="btn btn-sm btn-outline-success">
                                <i class="fab fa-whatsapp me-1"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/Views/partials/footer.php'; ?> 
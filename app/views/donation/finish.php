<?php require_once BASEPATH . '/app/views/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success display-1"></i>
                    </div>
                    <h2 class="mb-4">Terima Kasih Atas Donasi Anda!</h2>
                    <p class="lead mb-4">Donasi Anda telah berhasil dibuat. Status pembayaran Anda saat ini adalah: </p>
                    
                    <?php if ($donation['status'] === 'success'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> Pembayaran telah berhasil diverifikasi!
                        </div>
                    <?php elseif ($donation['status'] === 'pending'): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i> Menunggu pembayaran
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="fas fa-info-circle me-2"></i> <?= ucfirst($donation['status']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Detail Donasi</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-start"><strong>ID Donasi</strong></td>
                                    <td class="text-end"><?= $donation['id'] ?></td>
                                </tr>
                                <tr>
                                    <td class="text-start"><strong>Kampanye</strong></td>
                                    <td class="text-end"><?= $campaign['title'] ?></td>
                                </tr>
                                <tr>
                                    <td class="text-start"><strong>Jumlah</strong></td>
                                    <td class="text-end">Rp <?= number_format($donation['amount'], 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-start"><strong>Tanggal</strong></td>
                                    <td class="text-end"><?= date('d M Y H:i', strtotime($donation['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-start"><strong>Metode Pembayaran</strong></td>
                                    <td class="text-end">
                                        <?php
                                        $paymentMethod = $donation['payment_method'];
                                        if ($paymentMethod === 'bank_transfer') {
                                            echo 'Transfer Bank';
                                            if (!empty($donation['payment_channel'])) {
                                                echo ' - ' . strtoupper($donation['payment_channel']);
                                            }
                                        } elseif ($paymentMethod === 'e-wallet') {
                                            echo 'E-Wallet';
                                            if (!empty($donation['payment_channel'])) {
                                                echo ' - ' . strtoupper($donation['payment_channel']);
                                            }
                                        } else {
                                            echo ucfirst($paymentMethod);
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php if (!empty($donation['va_number'])): ?>
                                <tr>
                                    <td class="text-start"><strong>Nomor Virtual Account</strong></td>
                                    <td class="text-end"><?= $donation['va_number'] ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-start"><strong>Status</strong></td>
                                    <td class="text-end">
                                        <?php if ($donation['status'] === 'success'): ?>
                                            <span class="badge bg-success">Berhasil</span>
                                        <?php elseif ($donation['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
                                        <?php elseif ($donation['status'] === 'failed'): ?>
                                            <span class="badge bg-danger">Gagal</span>
                                        <?php elseif ($donation['status'] === 'canceled'): ?>
                                            <span class="badge bg-secondary">Dibatalkan</span>
                                        <?php elseif ($donation['status'] === 'expired'): ?>
                                            <span class="badge bg-secondary">Kedaluwarsa</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst($donation['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($donation['status'] === 'pending' && !empty($donation['payment_url'])): ?>
                    <div class="mb-4">
                        <a href="<?= $donation['payment_url'] ?>" class="btn btn-primary btn-lg" target="_blank">
                            <i class="fas fa-credit-card me-2"></i> Lanjutkan Pembayaran
                        </a>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Jika Anda telah melakukan pembayaran, mohon tunggu beberapa saat hingga sistem memverifikasi pembayaran Anda.
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="<?= BASE_URL ?>/campaign/detail/<?= $campaign['slug'] ?>" class="btn btn-outline-primary me-2">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Kampanye
                        </a>
                        
                        <?php if (isset($_SESSION['user'])): ?>
                        <a href="<?= BASE_URL ?>/donation/history" class="btn btn-outline-secondary">
                            <i class="fas fa-history me-2"></i> Riwayat Donasi
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted">
                    <i class="fas fa-heart text-danger"></i> Terima kasih telah menjadi bagian dari perubahan positif!
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/views/partials/footer.php'; ?> 
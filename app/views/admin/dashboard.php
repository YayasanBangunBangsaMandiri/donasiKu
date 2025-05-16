<?php require_once BASEPATH . '/app/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-donate text-primary me-2"></i> Total Donasi</h5>
                    <h2 class="display-6 fw-bold mb-0">Rp 0</h2>
                    <p class="text-muted">0 transaksi</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-hand-holding-heart text-success me-2"></i> Kampanye Aktif</h5>
                    <h2 class="display-6 fw-bold mb-0">0</h2>
                    <p class="text-muted">Dari total 0 kampanye</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-users text-info me-2"></i> Total Donatur</h5>
                    <h2 class="display-6 fw-bold mb-0">0</h2>
                    <p class="text-muted">0 donatur baru bulan ini</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-chart-line text-warning me-2"></i> Tingkat Konversi</h5>
                    <h2 class="display-6 fw-bold mb-0">0%</h2>
                    <p class="text-muted">0 / 0 pengunjung</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Grafik Donasi</h5>
                </div>
                <div class="card-body">
                    <div class="text-center p-5">
                        <i class="fas fa-chart-bar text-muted fa-3x mb-3"></i>
                        <h5>Belum ada data</h5>
                        <p class="text-muted">Data akan ditampilkan ketika donasi mulai masuk</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Donasi Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="text-center p-5">
                        <i class="fas fa-donate text-muted fa-3x mb-3"></i>
                        <h5>Belum ada donasi</h5>
                        <p class="text-muted">Donasi terbaru akan ditampilkan di sini</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Kampanye Terbaru</h5>
                    <a href="<?= BASE_URL ?>/admin/campaigns" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <div class="text-center p-5">
                        <i class="fas fa-hand-holding-heart text-muted fa-3x mb-3"></i>
                        <h5>Belum ada kampanye</h5>
                        <p class="text-muted">Kampanye akan ditampilkan di sini saat dibuat</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/views/partials/footer.php'; ?>

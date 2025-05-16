<?php require_once BASEPATH . '/app/views/partials/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <!-- Sidebar Menu -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i> Menu Pengguna</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= BASE_URL ?>/user/dashboard" class="list-group-item list-group-item-action active">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>/user/donations" class="list-group-item list-group-item-action">
                        <i class="fas fa-donate me-2"></i> Donasi Saya
                    </a>
                    <a href="<?= BASE_URL ?>/user/profile" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-edit me-2"></i> Edit Profil
                    </a>
                    <a href="<?= BASE_URL ?>/auth/logout" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Keluar
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="fw-bold">Selamat datang di DonasiKu!</h2>
                    <p class="text-muted">Lihat riwayat donasi Anda dan kelola akun dari sini.</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-donate text-primary me-2"></i> Donasi Saya</h5>
                            <div class="text-center py-4">
                                <i class="fas fa-hand-holding-heart text-muted fa-3x mb-3"></i>
                                <h5>Belum ada donasi</h5>
                                <p class="text-muted">Anda belum melakukan donasi apapun</p>
                                <a href="<?= BASE_URL ?>" class="btn btn-primary">Mulai Berdonasi</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-bell text-warning me-2"></i> Notifikasi</h5>
                            <div class="text-center py-4">
                                <i class="fas fa-bell-slash text-muted fa-3x mb-3"></i>
                                <h5>Tidak ada notifikasi</h5>
                                <p class="text-muted">Anda tidak memiliki notifikasi baru</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Kampanye yang Direkomendasikan</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-search text-muted fa-3x mb-3"></i>
                        <h5>Belum ada rekomendasi</h5>
                        <p class="text-muted">Rekomendasi akan muncul berdasarkan aktivitas Anda</p>
                        <a href="<?= BASE_URL ?>" class="btn btn-outline-primary">Jelajahi Kampanye</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/views/partials/footer.php'; ?> 
<?php require_once BASEPATH . '/app/views/partials/header.php'; ?>

<!-- Hero Section -->
<section class="hero bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4">Bantu Mereka yang Membutuhkan</h1>
                <p class="lead mb-4">DonateHub adalah platform donasi online yang menghubungkan Anda dengan berbagai kampanye sosial, kesehatan, pendidikan, dan kemanusiaan.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= BASE_URL ?>/campaign" class="btn btn-light btn-lg px-4">Lihat Kampanye</a>
                    <a href="<?= BASE_URL ?>/campaign/create" class="btn btn-outline-light btn-lg px-4">Buat Kampanye</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= BASE_URL ?>/public/img/hero-image.svg" alt="Hero Image" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Statistik -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="display-4 fw-bold text-primary mb-2">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <h3 class="counter mb-2">Rp <?= number_format($totalDonation, 0, ',', '.') ?></h3>
                        <p class="text-muted mb-0">Total Donasi Terkumpul</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="display-4 fw-bold text-primary mb-2">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="counter mb-2"><?= number_format($totalDonors, 0, ',', '.') ?></h3>
                        <p class="text-muted mb-0">Jumlah Donatur</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="display-4 fw-bold text-primary mb-2">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="counter mb-2"><?= count($campaigns) ?></h3>
                        <p class="text-muted mb-0">Kampanye Aktif</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Kampanye Pilihan -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Kampanye Pilihan</h2>
            <p class="text-muted">Bantu mereka yang membutuhkan melalui kampanye-kampanye berikut</p>
        </div>
        
        <div class="row g-4">
            <?php if (empty($campaigns)): ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Belum ada kampanye yang tersedia saat ini.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($campaigns as $campaign): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <img src="<?= BASE_URL ?>/public/uploads/<?= $campaign['featured_image'] ?>" class="card-img-top" alt="<?= $campaign['title'] ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary"><?= $campaign['category_name'] ?></span>
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        <?php
                                        $endDate = new DateTime($campaign['end_date']);
                                        $now = new DateTime();
                                        $interval = $now->diff($endDate);
                                        echo $interval->days . ' hari lagi';
                                        ?>
                                    </small>
                                </div>
                                <h5 class="card-title mb-3"><?= $campaign['title'] ?></h5>
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
                                <a href="<?= BASE_URL ?>/campaign/detail/<?= $campaign['slug'] ?>" class="btn btn-primary w-100">Donasi Sekarang</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?= BASE_URL ?>/campaign" class="btn btn-outline-primary btn-lg px-5">Lihat Semua Kampanye</a>
        </div>
    </div>
</section>

<!-- Cara Kerja -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Cara Kerja</h2>
            <p class="text-muted">Tiga langkah mudah untuk membantu sesama</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body">
                        <div class="display-4 text-primary mb-3">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4 class="card-title">1. Pilih Kampanye</h4>
                        <p class="card-text text-muted">Temukan kampanye yang sesuai dengan kepedulian Anda, dari kesehatan hingga pendidikan.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body">
                        <div class="display-4 text-primary mb-3">
                            <i class="fas fa-donate"></i>
                        </div>
                        <h4 class="card-title">2. Berikan Donasi</h4>
                        <p class="card-text text-muted">Donasikan sejumlah dana dengan mudah melalui berbagai metode pembayaran yang aman.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body">
                        <div class="display-4 text-primary mb-3">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4 class="card-title">3. Buat Perubahan</h4>
                        <p class="card-text text-muted">Lihat bagaimana donasi Anda membantu orang lain dan membuat perubahan nyata.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimoni -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Testimoni</h2>
            <p class="text-muted">Apa kata mereka tentang DonateHub</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3 text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text mb-4">"Platform donasi yang sangat mudah digunakan. Saya bisa membantu banyak orang dengan cepat dan aman. Terima kasih DonateHub!"</p>
                        <div class="d-flex align-items-center">
                            <img src="<?= BASE_URL ?>/public/img/testimonial-1.jpg" alt="Testimonial" class="rounded-circle me-3" width="50" height="50">
                            <div>
                                <h6 class="mb-0">Budi Santoso</h6>
                                <small class="text-muted">Donatur</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3 text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text mb-4">"Berkat DonateHub, kampanye pengobatan ibu saya berhasil mendapatkan dana yang cukup. Proses pencairan dana juga sangat transparan."</p>
                        <div class="d-flex align-items-center">
                            <img src="<?= BASE_URL ?>/public/img/testimonial-2.jpg" alt="Testimonial" class="rounded-circle me-3" width="50" height="50">
                            <div>
                                <h6 class="mb-0">Siti Rahma</h6>
                                <small class="text-muted">Penggalang Dana</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3 text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text mb-4">"Sebagai yayasan pendidikan, kami sangat terbantu dengan adanya DonateHub. Kami bisa menggalang dana untuk beasiswa siswa kurang mampu."</p>
                        <div class="d-flex align-items-center">
                            <img src="<?= BASE_URL ?>/public/img/testimonial-3.jpg" alt="Testimonial" class="rounded-circle me-3" width="50" height="50">
                            <div>
                                <h6 class="mb-0">Ahmad Fadli</h6>
                                <small class="text-muted">Yayasan Pendidikan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-3">Siap Membantu Sesama?</h2>
                <p class="lead mb-0">Bergabunglah dengan ribuan orang yang telah membuat perubahan melalui DonateHub.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?= BASE_URL ?>/campaign" class="btn btn-light btn-lg me-2 mb-2 mb-md-0">Donasi Sekarang</a>
                <a href="<?= BASE_URL ?>/campaign/create" class="btn btn-outline-light btn-lg">Buat Kampanye</a>
            </div>
        </div>
    </div>
</section>

<?php require_once BASEPATH . '/app/views/partials/footer.php'; ?> 
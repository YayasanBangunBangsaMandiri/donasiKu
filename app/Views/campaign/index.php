<?php
// Mulai output buffering
ob_start();
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Kampanye</li>
        </ol>
    </nav>
    
    <!-- Page Title and Filter -->
    <div class="row align-items-center mb-4">
        <div class="col-lg-6 mb-3 mb-lg-0">
            <h1 class="mb-0">Kampanye Donasi</h1>
            <p class="text-muted">Temukan kampanye yang ingin Anda dukung</p>
        </div>
        <div class="col-lg-6">
            <form id="campaign-filter-form" action="<?= BASE_URL ?>/campaign" method="GET" class="d-flex flex-wrap gap-2">
                <div class="input-group flex-grow-1">
                    <input type="text" class="form-control" placeholder="Cari kampanye..." name="keyword" value="<?= isset($keyword) ? $keyword : '' ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
                <select class="form-select" name="category_id" id="category_id" style="width: auto;">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= (isset($categoryId) && $categoryId == $category['id']) ? 'selected' : '' ?>>
                            <?= $category['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
    
    <!-- Campaign List -->
    <div class="row">
        <?php if (empty($campaigns)): ?>
            <div class="col-12 text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-search fa-4x text-muted"></i>
                </div>
                <h3>Tidak Ada Kampanye Ditemukan</h3>
                <p class="text-muted">Tidak ada kampanye yang sesuai dengan kriteria pencarian Anda.</p>
                <a href="<?= BASE_URL ?>/campaign" class="btn btn-outline-primary mt-3">Lihat Semua Kampanye</a>
            </div>
        <?php else: ?>
            <?php foreach ($campaigns as $campaign): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?= BASE_URL ?>/<?= $campaign['featured_image'] ?>" class="card-img-top" alt="<?= $campaign['title'] ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary"><?= $campaign['category_name'] ?></span>
                                <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($campaign['created_at'])) ?></small>
                            </div>
                            
                            <h5 class="card-title"><?= $campaign['title'] ?></h5>
                            <p class="card-text text-muted small mb-3"><?= $campaign['short_description'] ?></p>
                            
                            <?php 
                            $targetAmount = $campaign['target_amount'];
                            $currentAmount = $campaign['current_amount'];
                            $percentage = ($currentAmount / $targetAmount) * 100;
                            $percentage = min(100, $percentage);
                            ?>
                            
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <small class="text-muted">Terkumpul: Rp <?= number_format($currentAmount, 0, ',', '.') ?></small>
                                <small class="text-muted"><?= round($percentage) ?>%</small>
                            </div>
                            
                            <div class="mt-auto d-grid gap-2">
                                <a href="<?= BASE_URL ?>/campaign/detail/<?= $campaign['slug'] ?>" class="btn btn-outline-primary">Lihat Detail</a>
                                <a href="<?= BASE_URL ?>/donation/form/<?= $campaign['slug'] ?>" class="btn btn-primary">Donasi Sekarang</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Call to Action -->
    <div class="mt-5 p-4 bg-light rounded text-center">
        <h3>Ingin Membuat Kampanye Donasi?</h3>
        <p class="mb-4">Bantu lebih banyak orang dengan membuat kampanye donasi Anda sendiri</p>
        <a href="<?= BASE_URL ?>/auth/register" class="btn btn-primary">Mulai Kampanye Sekarang</a>
    </div>
</div>

<?php
// Ambil konten dari buffer
$content = ob_get_clean();

// Render layout dengan konten
include 'app/Views/layouts/main.php';
?>
 
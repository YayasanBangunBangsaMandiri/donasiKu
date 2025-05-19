<?php
// Mulai output buffering
ob_start();
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/campaign" class="text-decoration-none">Kampanye</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $campaign['title'] ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Campaign Header -->
            <div class="mb-4">
                <h1 class="mb-2"><?= $campaign['title'] ?></h1>
                <p class="text-muted mb-3"><?= $campaign['short_description'] ?></p>
                
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?= $campaign['category_name'] ?></span>
                        <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($campaign['created_at'])) ?></small>
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        <a href="<?= BASE_URL ?>/campaign/share/<?= $campaign['slug'] ?>/facebook" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/campaign/share/<?= $campaign['slug'] ?>/twitter" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/campaign/share/<?= $campaign['slug'] ?>/whatsapp" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Campaign Image -->
            <div class="mb-4">
                <img src="<?= BASE_URL ?>/public/uploads/<?= $campaign['featured_image'] ?>" alt="<?= $campaign['title'] ?>" class="img-fluid rounded w-100" style="max-height: 400px; object-fit: cover;">
            </div>
            
            <!-- Campaign Progress -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h3 class="mb-0">Rp <?= number_format($campaign['current_amount'], 0, ',', '.') ?></h3>
                            <p class="text-muted mb-0">terkumpul dari target Rp <?= number_format($campaign['goal_amount'], 0, ',', '.') ?></p>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-2">
                                <span><?= $stats['total_donors'] ?> Donatur</span>
                                <span><?= $stats['progress_percentage'] ?>% Tercapai</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $stats['progress_percentage'] ?>%;" 
                                    aria-valuenow="<?= $stats['progress_percentage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 d-grid">
                    <a href="<?= BASE_URL ?>/donate.php?campaign=<?= $campaign['slug'] ?>" class="btn btn-primary btn-lg">Donasi Sekarang</a>
                </div>
            </div>
            
            <!-- Campaign Description -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Detail Kampanye</h4>
                </div>
                <div class="card-body">
                    <div class="campaign-content">
                        <?= $campaign['description'] ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Campaign Organizer -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Penggalang Dana</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-user fa-2x text-muted"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1"><?= $campaign['creator_name'] ?></h6>
                            <p class="text-muted small mb-0">Penggalang Dana</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Donations -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Donasi Terbaru</h5>
                    <span class="badge bg-primary rounded-pill"><?= $stats['total_donations'] ?></span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($recentDonations)): ?>
                            <li class="list-group-item p-3 text-center text-muted">
                                Belum ada donasi untuk kampanye ini
                            </li>
                        <?php else: ?>
                            <?php foreach ($recentDonations as $donation): ?>
                                <li class="list-group-item p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-0">
                                                <?= $donation['is_anonymous'] ? 'Anonim' : $donation['name'] ?>
                                            </h6>
                                            <p class="text-muted small mb-0">
                                                <?= !empty($donation['message']) ? '"' . substr($donation['message'], 0, 50) . (strlen($donation['message']) > 50 ? '...' : '') . '"' : 'Tanpa pesan' ?>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">Rp <?= number_format($donation['amount'], 0, ',', '.') ?></div>
                                            <small class="text-muted"><?= date('d M Y', strtotime($donation['created_at'])) ?></small>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Related Campaigns -->
            <?php if (!empty($relatedCampaigns)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Kampanye Terkait</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($relatedCampaigns as $relCampaign): ?>
                            <li class="list-group-item p-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <img src="<?= BASE_URL ?>/public/uploads/<?= $relCampaign['featured_image'] ?>" 
                                            class="rounded" alt="<?= $relCampaign['title'] ?>" 
                                            style="width: 70px; height: 50px; object-fit: cover;">
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-1 line-clamp-2">
                                            <a href="<?= BASE_URL ?>/campaign/detail/<?= $relCampaign['slug'] ?>" 
                                                class="text-decoration-none text-dark"><?= $relCampaign['title'] ?></a>
                                        </h6>
                                        <div class="progress mb-1" style="height: 5px;">
                                            <?php 
                                            $relPercentage = $relCampaign['goal_amount'] > 0 ? 
                                                min(100, round(($relCampaign['current_amount'] / $relCampaign['goal_amount']) * 100)) : 0;
                                            ?>
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $relPercentage ?>%;" 
                                                aria-valuenow="<?= $relPercentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small class="text-muted">
                                            Rp <?= number_format($relCampaign['current_amount'], 0, ',', '.') ?> terkumpul
                                        </small>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Add some CSS for line clamping
?>
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
.campaign-content img {
    max-width: 100%;
    height: auto;
}
</style>

<?php
// Ambil konten dari buffer
$content = ob_get_clean();

// Render layout dengan konten
include 'app/Views/layouts/main.php';
?> 
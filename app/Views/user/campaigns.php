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
                            <a href="<?= BASE_URL ?>/user/donations" class="nav-link">
                                <i class="fas fa-donate me-2"></i> Donasi Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/user/campaigns" class="nav-link active">
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
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Kampanye Saya</h5>
                        <a href="<?= BASE_URL ?>/campaign/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Buat Kampanye
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($campaigns)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-hand-holding-heart text-muted fa-4x mb-3"></i>
                            <h4 class="mb-3">Belum Ada Kampanye</h4>
                            <p class="text-muted mb-4">Anda belum membuat kampanye penggalangan dana</p>
                            <a href="<?= BASE_URL ?>/campaign/create" class="btn btn-primary">Buat Kampanye Sekarang</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($campaigns as $campaign): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="position-relative">
                                            <img src="<?= BASE_URL ?>/public/uploads/<?= $campaign['featured_image'] ?>" 
                                                class="card-img-top" alt="<?= $campaign['title'] ?>" 
                                                style="height: 180px; object-fit: cover;">
                                            
                                            <?php 
                                            $statusClass = '';
                                            $statusText = '';
                                            
                                            switch($campaign['status']) {
                                                case 'active':
                                                    $statusClass = 'success';
                                                    $statusText = 'Aktif';
                                                    break;
                                                case 'pending':
                                                    $statusClass = 'warning';
                                                    $statusText = 'Menunggu Persetujuan';
                                                    break;
                                                case 'ended':
                                                    $statusClass = 'secondary';
                                                    $statusText = 'Berakhir';
                                                    break;
                                                case 'rejected':
                                                    $statusClass = 'danger';
                                                    $statusText = 'Ditolak';
                                                    break;
                                                default:
                                                    $statusClass = 'info';
                                                    $statusText = ucfirst($campaign['status']);
                                            }
                                            ?>
                                            <span class="position-absolute top-0 end-0 badge bg-<?= $statusClass ?> m-2 px-2 py-1">
                                                <?= $statusText ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?= $campaign['title'] ?></h5>
                                            <div class="mb-3">
                                                <div class="progress mb-2" style="height: 5px;">
                                                    <?php 
                                                    $percentage = min(100, ($campaign['current_amount'] / $campaign['goal_amount']) * 100);
                                                    ?>
                                                    <div class="progress-bar bg-primary" style="width: <?= $percentage ?>%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center small">
                                                    <span>Terkumpul: <?= number_format($percentage, 0) ?>%</span>
                                                    <span>Rp <?= number_format($campaign['current_amount'], 0, ',', '.') ?></span>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                                                <span><i class="far fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($campaign['end_date'])) ?></span>
                                                <span><i class="fas fa-users me-1"></i> <?= $campaign['donor_count'] ?? 0 ?> donatur</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="<?= BASE_URL ?>/campaign/detail/<?= $campaign['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                                    <i class="fas fa-eye me-1"></i> Lihat
                                                </a>
                                                <a href="<?= BASE_URL ?>/campaign/edit/<?= $campaign['id'] ?>" class="btn btn-sm btn-outline-secondary flex-grow-1">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($currentPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= BASE_URL ?>/user/campaigns?page=<?= $currentPage - 1 ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($totalPages, $startPage + 4);
                                    if ($endPage - $startPage < 4) {
                                        $startPage = max(1, $endPage - 4);
                                    }
                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= BASE_URL ?>/user/campaigns?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= BASE_URL ?>/user/campaigns?page=<?= $currentPage + 1 ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/Views/partials/footer.php'; ?> 
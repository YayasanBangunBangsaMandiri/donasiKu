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
                            <a href="<?= BASE_URL ?>/user/dashboard" class="nav-link active">
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
            <!-- Welcome Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="card-title">Selamat Datang, <?= $_SESSION['user']['name'] ?>!</h4>
                    <p class="card-text">Selamat datang di dashboard DonasiKu. Di sini Anda dapat mengelola profil, melihat riwayat donasi, dan membuat kampanye baru.</p>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                    <i class="fas fa-donate text-primary"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">Total Donasi</p>
                                    <h4 class="mb-0">Rp <?= number_format($totalDonated, 0, ',', '.') ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                    <i class="fas fa-hand-holding-heart text-success"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">Kampanye Saya</p>
                                    <h4 class="mb-0"><?= count($myCampaigns) ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Donations -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Donasi Terakhir</h5>
                        <a href="<?= BASE_URL ?>/user/donations" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($myDonations)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-donate text-muted fa-3x mb-3"></i>
                            <p class="text-muted">Anda belum melakukan donasi</p>
                            <a href="<?= BASE_URL ?>/campaign" class="btn btn-primary">Donasi Sekarang</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kampanye</th>
                                        <th>Tanggal</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myDonations as $donation): ?>
                                        <tr>
                                            <td><?= $donation['campaign_title'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($donation['created_at'])) ?></td>
                                            <td>Rp <?= number_format($donation['amount'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php 
                                                $statusClass = '';
                                                $statusText = '';
                                                
                                                switch($donation['status']) {
                                                    case 'pending':
                                                        $statusClass = 'warning';
                                                        $statusText = 'Menunggu';
                                                        break;
                                                    case 'success':
                                                        $statusClass = 'success';
                                                        $statusText = 'Berhasil';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'danger';
                                                        $statusText = 'Dibatalkan';
                                                        break;
                                                    default:
                                                        $statusClass = 'secondary';
                                                        $statusText = ucfirst($donation['status']);
                                                }
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- My Campaigns -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Kampanye Saya</h5>
                        <a href="<?= BASE_URL ?>/user/campaigns" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($myCampaigns)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-hand-holding-heart text-muted fa-3x mb-3"></i>
                            <p class="text-muted">Anda belum membuat kampanye</p>
                            <a href="<?= BASE_URL ?>/campaign/create" class="btn btn-primary">Buat Kampanye</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($myCampaigns as $campaign): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <img src="<?= BASE_URL ?>/public/uploads/<?= $campaign['featured_image'] ?>" class="card-img-top" alt="<?= $campaign['title'] ?>" style="height: 140px; object-fit: cover;">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= $campaign['title'] ?></h6>
                                            <div class="progress mb-2" style="height: 5px;">
                                                <?php 
                                                $percentage = ($campaign['current_amount'] / $campaign['goal_amount']) * 100;
                                                $percentage = min(100, $percentage);
                                                ?>
                                                <div class="progress-bar bg-primary" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                            <div class="d-flex justify-content-between mb-3">
                                                <small class="text-muted">Terkumpul: <?= number_format($percentage, 0) ?>%</small>
                                                <small class="text-muted">Rp <?= number_format($campaign['current_amount'], 0, ',', '.') ?></small>
                                            </div>
                                            <a href="<?= BASE_URL ?>/campaign/detail/<?= $campaign['id'] ?>" class="btn btn-sm btn-outline-primary w-100">Lihat Detail</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/Views/partials/footer.php'; ?> 
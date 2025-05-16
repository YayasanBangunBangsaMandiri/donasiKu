<?php require_once BASEPATH . '/app/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Dashboard Admin</h2>
            <p class="text-muted">Selamat datang, <?= $_SESSION['user']['name'] ?>!</p>
        </div>
    </div>
    
    <!-- Statistik Donasi -->
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Total Donasi</h6>
                            <h3 class="mb-0">Rp <?= number_format($donationStats['total_success'], 0, ',', '.') ?></h3>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="fas fa-money-bill-wave text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Jumlah Transaksi</h6>
                            <h3 class="mb-0"><?= number_format($donationStats['count_success'], 0, ',', '.') ?></h3>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="fas fa-chart-line text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Jumlah Donatur</h6>
                            <h3 class="mb-0"><?= number_format($donationStats['unique_donors'], 0, ',', '.') ?></h3>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="fas fa-users text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Kampanye Aktif</h6>
                            <h3 class="mb-0"><?= count($activeCampaigns) ?></h3>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="fas fa-file-alt text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Donasi Terbaru -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Donasi Terbaru</h5>
                        <a href="<?= BASE_URL ?>/admin/donations" class="btn btn-sm btn-primary">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Nama</th>
                                    <th scope="col">Kampanye</th>
                                    <th scope="col">Jumlah</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentDonations)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">Belum ada data donasi</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentDonations as $donation): ?>
                                        <tr>
                                            <td><?= $donation['id'] ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($donation['created_at'])) ?></td>
                                            <td>
                                                <?php if ($donation['is_anonymous']): ?>
                                                    <span class="text-muted">Anonim</span>
                                                <?php else: ?>
                                                    <?= $donation['name'] ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $donation['campaign_title'] ?></td>
                                            <td>Rp <?= number_format($donation['amount'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php if ($donation['status'] === 'success'): ?>
                                                    <span class="badge bg-success">Berhasil</span>
                                                <?php elseif ($donation['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= ucfirst($donation['status']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/admin/donation/<?= $donation['id'] ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kampanye Aktif -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Kampanye Aktif</h5>
                        <a href="<?= BASE_URL ?>/admin/campaigns" class="btn btn-sm btn-primary">Kelola Kampanye</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($activeCampaigns)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Belum ada kampanye aktif</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($activeCampaigns as $campaign): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><?= $campaign['title'] ?></h6>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($campaign['end_date'])) ?></small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <?php 
                                            $percentage = 0;
                                            if ($campaign['goal_amount'] > 0) {
                                                $percentage = min(($campaign['current_amount'] / $campaign['goal_amount']) * 100, 100);
                                            }
                                        ?>
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small>Rp <?= number_format($campaign['current_amount'], 0, ',', '.') ?></small>
                                        <small><?= number_format($percentage, 1) ?>%</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent border-0 text-center">
                    <a href="<?= BASE_URL ?>/admin/add-campaign" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-2"></i> Tambah Kampanye Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/views/partials/footer.php'; ?>

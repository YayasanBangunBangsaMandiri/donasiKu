<?php require_once BASEPATH . '/app/Views/admin/header.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manajemen Kampanye</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Kampanye</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Bar -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Filter Kampanye</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL; ?>/admin/campaigns">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="active" <?= isset($status) && $status == 'active' ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="pending" <?= isset($status) && $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="ended" <?= isset($status) && $status == 'ended' ? 'selected' : ''; ?>>Berakhir</option>
                                        <option value="rejected" <?= isset($status) && $status == 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <select class="form-control select2" name="category_id">
                                        <option value="">Semua Kategori</option>
                                        <?php 
                                        $categories = $this->db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
                                        foreach ($categories as $category) : 
                                        ?>
                                            <option value="<?= $category['id']; ?>" <?= isset($_GET['category_id']) && $_GET['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                <?= $category['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Pencarian</label>
                                    <input type="text" class="form-control" name="search" placeholder="Judul / Deskripsi / Penggalang" value="<?= $_GET['search'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 d-flex">
                                <div class="ml-auto">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-1"></i> Filter
                                    </button>
                                    <a href="<?= BASE_URL; ?>/admin/campaigns" class="btn btn-default">
                                        <i class="fas fa-sync-alt mr-1"></i> Reset
                                    </a>
                                    <a href="<?= BASE_URL; ?>/admin/add-campaign" class="btn btn-success">
                                        <i class="fas fa-plus mr-1"></i> Tambah Kampanye
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Campaigns List -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Kampanye</th>
                                    <th style="width: 150px;">Penggalang</th>
                                    <th style="width: 120px;">Target</th>
                                    <th style="width: 120px;">Terkumpul</th>
                                    <th style="width: 120px;">Deadline</th>
                                    <th style="width: 100px;">Status</th>
                                    <th style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($campaigns)) : ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">Tidak ada data kampanye yang sesuai dengan filter</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($campaigns as $index => $campaign) : ?>
                                        <tr>
                                            <td><?= $campaign['id']; ?></td>
                                            <td>
                                                <div class="d-flex">
                                                    <div style="width: 60px; height: 45px; overflow: hidden; margin-right: 10px;">
                                                        <img src="<?= BASE_URL; ?>/uploads/<?= $campaign['featured_image']; ?>" alt="<?= htmlspecialchars($campaign['title']); ?>" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($campaign['title']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?= $campaign['category_name']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($campaign['organizer_name']); ?></td>
                                            <td>Rp <?= number_format($campaign['goal_amount'], 0, ',', '.'); ?></td>
                                            <td>
                                                Rp <?= number_format($campaign['collected_amount'], 0, ',', '.'); ?>
                                                <div class="progress progress-xs mt-1">
                                                    <?php $percentage = min(100, round(($campaign['collected_amount'] / $campaign['goal_amount']) * 100)); ?>
                                                    <div class="progress-bar bg-success" style="width: <?= $percentage; ?>%"></div>
                                                </div>
                                                <small class="text-muted"><?= $percentage; ?>%</small>
                                            </td>
                                            <td>
                                                <?= date('d M Y', strtotime($campaign['end_date'])); ?>
                                                <?php 
                                                $today = strtotime(date('Y-m-d'));
                                                $end = strtotime($campaign['end_date']);
                                                $daysLeft = ceil(($end - $today) / (60 * 60 * 24));
                                                
                                                if ($daysLeft > 0) {
                                                    echo '<br><small class="text-muted">' . $daysLeft . ' hari lagi</small>';
                                                } else {
                                                    echo '<br><small class="text-danger">Berakhir</small>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusLabels = [
                                                    'active' => '<span class="badge badge-success">Aktif</span>',
                                                    'pending' => '<span class="badge badge-warning">Pending</span>',
                                                    'ended' => '<span class="badge badge-secondary">Berakhir</span>',
                                                    'rejected' => '<span class="badge badge-danger">Ditolak</span>',
                                                ];
                                                echo $statusLabels[$campaign['status']] ?? $campaign['status'];
                                                
                                                if ($campaign['is_featured']) {
                                                    echo '<br><small class="badge badge-info mt-1">Featured</small>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?= BASE_URL; ?>/campaign/<?= $campaign['slug']; ?>" target="_blank" class="btn btn-sm btn-info" title="Lihat">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL; ?>/admin/edit-campaign/<?= $campaign['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-warning change-status" data-id="<?= $campaign['id']; ?>" data-status="<?= $campaign['status']; ?>" title="Ubah Status">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger delete-campaign" data-id="<?= $campaign['id']; ?>" data-title="<?= htmlspecialchars($campaign['title']); ?>" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-right">
                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <li class="page-item <?= $currentPage == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= BASE_URL; ?>/admin/campaigns?page=<?= $i; ?><?= isset($status) ? '&status=' . $status : ''; ?><?= isset($_GET['category_id']) ? '&category_id=' . $_GET['category_id'] : ''; ?><?= isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Status Kampanye</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="changeStatusForm" method="POST" action="<?= BASE_URL; ?>/admin/update-campaign-status">
                <div class="modal-body">
                    <input type="hidden" name="campaign_id" id="campaignId">
                    
                    <div class="form-group">
                        <label for="campaignStatus">Status Kampanye</label>
                        <select class="form-control" id="campaignStatus" name="status" required>
                            <option value="active">Aktif</option>
                            <option value="pending">Pending</option>
                            <option value="ended">Berakhir</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="statusNote">Catatan (opsional)</label>
                        <textarea class="form-control" id="statusNote" name="note" rows="3" placeholder="Tambahkan alasan perubahan status (akan disampaikan ke penggalang dana)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="sendNotification" name="send_notification" value="1" checked>
                            <label class="custom-control-label" for="sendNotification">Kirim notifikasi kepada penggalang dana</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Campaign Modal -->
<div class="modal fade" id="deleteCampaignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Kampanye</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin akan menghapus kampanye "<span id="campaignTitle"></span>"?</p>
                <p class="text-danger"><strong>Perhatian:</strong> Menghapus kampanye juga akan menghapus semua donasi yang terkait. Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteCampaignForm" method="POST" action="<?= BASE_URL; ?>/admin/delete-campaign">
                    <input type="hidden" name="campaign_id" id="deleteCampaignId">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        
        // Change status button
        $('.change-status').click(function() {
            const id = $(this).data('id');
            const currentStatus = $(this).data('status');
            
            $('#campaignId').val(id);
            $('#campaignStatus').val(currentStatus);
            $('#changeStatusModal').modal('show');
        });
        
        // Delete campaign button
        $('.delete-campaign').click(function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            
            $('#deleteCampaignId').val(id);
            $('#campaignTitle').text(title);
            $('#deleteCampaignModal').modal('show');
        });
    });
</script>

<?php require_once BASEPATH . '/app/Views/admin/footer.php'; ?>

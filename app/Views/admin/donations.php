<?php require_once BASEPATH . '/app/Views/admin/header.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manajemen Donasi</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Donasi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Box -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Filter Donasi</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL; ?>/admin/donations">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kampanye</label>
                                    <select class="form-control select2" name="campaign_id">
                                        <option value="">Semua Kampanye</option>
                                        <?php foreach ($campaigns as $campaign) : ?>
                                            <option value="<?= $campaign['id']; ?>" <?= isset($filters['campaign_id']) && $filters['campaign_id'] == $campaign['id'] ? 'selected' : ''; ?>>
                                                <?= $campaign['title']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="pending" <?= isset($filters['status']) && $filters['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="success" <?= isset($filters['status']) && $filters['status'] == 'success' ? 'selected' : ''; ?>>Success</option>
                                        <option value="failed" <?= isset($filters['status']) && $filters['status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Metode Pembayaran</label>
                                    <select class="form-control" name="payment_method">
                                        <option value="">Semua Metode</option>
                                        <option value="bank_transfer" <?= isset($filters['payment_method']) && $filters['payment_method'] == 'bank_transfer' ? 'selected' : ''; ?>>Transfer Bank</option>
                                        <option value="credit_card" <?= isset($filters['payment_method']) && $filters['payment_method'] == 'credit_card' ? 'selected' : ''; ?>>Kartu Kredit</option>
                                        <option value="ewallet" <?= isset($filters['payment_method']) && $filters['payment_method'] == 'ewallet' ? 'selected' : ''; ?>>E-Wallet</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Pencarian</label>
                                    <input type="text" class="form-control" name="search" placeholder="Nama / Email / Kode Donasi" value="<?= $filters['search'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tanggal Dari</label>
                                    <input type="date" class="form-control" name="date_from" value="<?= $filters['date_from'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tanggal Sampai</label>
                                    <input type="date" class="form-control" name="date_to" value="<?= $filters['date_to'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-group mb-0 ml-auto">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-1"></i> Filter
                                    </button>
                                    <a href="<?= BASE_URL; ?>/admin/donations" class="btn btn-default">
                                        <i class="fas fa-sync-alt mr-1"></i> Reset
                                    </a>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-download mr-1"></i> Export Data
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" id="exportExcel">
                                                <i class="fas fa-file-excel mr-2"></i> Excel (.xls)
                                            </a>
                                            <a class="dropdown-item" href="#" id="exportPdf">
                                                <i class="fas fa-file-pdf mr-2"></i> PDF
                                            </a>
                                            <a class="dropdown-item" href="#" id="exportCsv">
                                                <i class="fas fa-file-csv mr-2"></i> CSV
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Donations List -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>Donatur</th>
                                    <th>Kampanye</th>
                                    <th>Jumlah</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($donations)) : ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">Tidak ada data donasi yang sesuai dengan filter</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($donations as $donation) : ?>
                                        <tr>
                                            <td><?= $donation['id']; ?></td>
                                            <td><?= date('d M Y H:i', strtotime($donation['created_at'])); ?></td>
                                            <td>
                                                <?= htmlspecialchars($donation['name'] ?? ''); ?><br>
                                                <small class="text-muted"><?= htmlspecialchars($donation['email'] ?? ''); ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($donation['campaign_title'] ?? 'N/A'); ?></td>
                                            <td><b>Rp <?= number_format($donation['amount'], 0, ',', '.'); ?></b></td>
                                            <td>
                                                <?php
                                                $paymentLabels = [
                                                    'bank_transfer' => '<span class="badge badge-info">Transfer Bank</span>',
                                                    'credit_card' => '<span class="badge badge-primary">Kartu Kredit</span>',
                                                    'ewallet' => '<span class="badge badge-warning">E-Wallet</span>',
                                                ];
                                                echo $paymentLabels[$donation['payment_method']] ?? $donation['payment_method'];
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusLabels = [
                                                    'pending' => '<span class="badge badge-warning">Pending</span>',
                                                    'success' => '<span class="badge badge-success">Success</span>',
                                                    'failed' => '<span class="badge badge-danger">Failed</span>',
                                                ];
                                                echo $statusLabels[$donation['status']] ?? $donation['status'];
                                                ?>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL; ?>/admin/donation/<?= $donation['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($donation['status'] === 'pending') : ?>
                                                    <button type="button" class="btn btn-sm btn-success confirm-payment" data-id="<?= $donation['id']; ?>">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-danger delete-donation" data-id="<?= $donation['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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
                        <?php for ($i = 1; $i <= $pagination['last_page']; $i++) : ?>
                            <li class="page-item <?= $pagination['current_page'] == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= BASE_URL; ?>/admin/donations?page=<?= $i; ?><?= isset($filters['campaign_id']) ? '&campaign_id=' . $filters['campaign_id'] : ''; ?><?= isset($filters['status']) ? '&status=' . $filters['status'] : ''; ?><?= isset($filters['payment_method']) ? '&payment_method=' . $filters['payment_method'] : ''; ?><?= isset($filters['search']) ? '&search=' . $filters['search'] : ''; ?><?= isset($filters['date_from']) ? '&date_from=' . $filters['date_from'] : ''; ?><?= isset($filters['date_to']) ? '&date_to=' . $filters['date_to'] : ''; ?>">
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

<!-- Confirm Payment Modal -->
<div class="modal fade" id="confirmPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin akan mengkonfirmasi pembayaran donasi ini?</p>
            </div>
            <div class="modal-footer">
                <form id="confirmPaymentForm" method="POST" action="<?= BASE_URL; ?>/admin/confirm-payment">
                    <input type="hidden" name="donation_id" id="confirmDonationId">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Konfirmasi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Donation Modal -->
<div class="modal fade" id="deleteDonationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Donasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin akan menghapus data donasi ini?</p>
                <p class="text-danger"><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteDonationForm" method="POST" action="<?= BASE_URL; ?>/admin/delete-donation">
                    <input type="hidden" name="donation_id" id="deleteDonationId">
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
        
        // Confirm payment button
        $('.confirm-payment').click(function() {
            const id = $(this).data('id');
            $('#confirmDonationId').val(id);
            $('#confirmPaymentModal').modal('show');
        });
        
        // Delete donation button
        $('.delete-donation').click(function() {
            const id = $(this).data('id');
            $('#deleteDonationId').val(id);
            $('#deleteDonationModal').modal('show');
        });
        
        // Export functions
        function exportData(format) {
            let url = '<?= BASE_URL; ?>/admin/export-donations?format=' + format + '&';
            const form = $('#exportExcel').closest('form');
            const formData = form.serialize();
            
            if (formData) {
                url += formData;
            }
            
            window.location.href = url;
        }
        
        // Export to Excel
        $('#exportExcel').click(function(e) {
            e.preventDefault();
            exportData('excel');
        });
        
        // Export to PDF
        $('#exportPdf').click(function(e) {
            e.preventDefault();
            exportData('pdf');
        });
        
        // Export to CSV
        $('#exportCsv').click(function(e) {
            e.preventDefault();
            exportData('csv');
        });
    });
</script>

<?php require_once BASEPATH . '/app/Views/admin/footer.php'; ?>

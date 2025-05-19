<?php require_once BASEPATH . '/app/Views/admin/header.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detail Donasi #<?= $donation['id']; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/admin/donations">Donasi</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Donation Info -->
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Informasi Donasi</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 140px">ID Donasi</th>
                                            <td><?= $donation['id']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal</th>
                                            <td><?= date('d M Y H:i', strtotime($donation['created_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
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
                                        </tr>
                                        <tr>
                                            <th>Jumlah</th>
                                            <td><strong>Rp <?= number_format($donation['amount'], 0, ',', '.'); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Metode Bayar</th>
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
                                        </tr>
                                        <?php if (!empty($donation['paid_at'])) : ?>
                                        <tr>
                                            <th>Tanggal Bayar</th>
                                            <td><?= date('d M Y H:i', strtotime($donation['paid_at'])); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 140px">Nama Donatur</th>
                                            <td><?= htmlspecialchars($donation['name'] ?? ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td><?= htmlspecialchars($donation['email'] ?? ''); ?></td>
                                        </tr>
                                        <?php if (!empty($donation['phone'])) : ?>
                                        <tr>
                                            <th>Telp/WA</th>
                                            <td><?= htmlspecialchars($donation['phone']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <th>Kampanye</th>
                                            <td>
                                                <a href="<?= BASE_URL; ?>/campaign/<?= $donation['campaign']['slug']; ?>" target="_blank">
                                                    <?= htmlspecialchars($donation['campaign']['title']); ?>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Anonim</th>
                                            <td><?= $donation['is_anonymous'] ? 'Ya' : 'Tidak'; ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <?php if (!empty($donation['message'])) : ?>
                            <div class="mt-4">
                                <h5>Pesan Donatur:</h5>
                                <div class="p-3 bg-light rounded">
                                    <?= nl2br(htmlspecialchars($donation['message'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($donation['payment_data'])) : ?>
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Data Pembayaran</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php
                            $paymentData = json_decode($donation['payment_data'], true);
                            if (is_array($paymentData)) :
                            ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <?php foreach ($paymentData as $key => $value) : ?>
                                    <tr>
                                        <th style="width: 200px"><?= ucwords(str_replace('_', ' ', $key)); ?></th>
                                        <td>
                                            <?php
                                            if (is_array($value)) {
                                                echo '<pre>' . json_encode($value, JSON_PRETTY_PRINT) . '</pre>';
                                            } else {
                                                echo is_string($value) ? htmlspecialchars($value) : $value;
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                            <?php else : ?>
                            <pre><?= htmlspecialchars($donation['payment_data']); ?></pre>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Action Panel -->
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Tindakan</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($donation['status'] === 'pending') : ?>
                            <button type="button" class="btn btn-success btn-block confirm-payment" data-id="<?= $donation['id']; ?>">
                                <i class="fas fa-check mr-1"></i> Konfirmasi Pembayaran
                            </button>
                            <hr>
                            <?php endif; ?>

                            <a href="<?= BASE_URL; ?>/admin/donations" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                            </a>

                            <button type="button" class="btn btn-info btn-block" id="sendNotification" data-id="<?= $donation['id']; ?>">
                                <i class="fas fa-envelope mr-1"></i> Kirim Notifikasi
                            </button>
                            
                            <div class="btn-group btn-block mt-2">
                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-download mr-1"></i> Download Receipt
                                </button>
                                <div class="dropdown-menu w-100">
                                    <a class="dropdown-item" href="<?= BASE_URL; ?>/admin/download-receipt/<?= $donation['id']; ?>?format=pdf">
                                        <i class="fas fa-file-pdf text-danger mr-1"></i> PDF
                                    </a>
                                    <a class="dropdown-item" href="<?= BASE_URL; ?>/admin/download-receipt/<?= $donation['id']; ?>?format=excel">
                                        <i class="fas fa-file-excel text-success mr-1"></i> Excel
                                    </a>
                                </div>
                            </div>

                            <button type="button" class="btn btn-danger btn-block mt-2 delete-donation" data-id="<?= $donation['id']; ?>">
                                <i class="fas fa-trash mr-1"></i> Hapus Donasi
                            </button>
                        </div>
                    </div>

                    <!-- Transaction Log -->
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Riwayat Transaksi</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="timeline timeline-inverse p-3">
                                <li>
                                    <i class="fas fa-plus bg-primary"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($donation['created_at'])); ?></span>
                                        <h3 class="timeline-header">Donasi Dibuat</h3>
                                    </div>
                                </li>

                                <?php if (!empty($donation['payment_status_updated_at'])) : ?>
                                <li>
                                    <i class="fas fa-sync bg-info"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($donation['payment_status_updated_at'])); ?></span>
                                        <h3 class="timeline-header">Status Pembayaran Diperbarui</h3>
                                        <div class="timeline-body">
                                            Status diubah menjadi: <?= $statusLabels[$donation['status']] ?? $donation['status']; ?>
                                        </div>
                                    </div>
                                </li>
                                <?php endif; ?>

                                <?php if (!empty($donation['paid_at'])) : ?>
                                <li>
                                    <i class="fas fa-check bg-success"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($donation['paid_at'])); ?></span>
                                        <h3 class="timeline-header">Pembayaran Diterima</h3>
                                    </div>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
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
                <p>Apakah Anda yakin akan mengkonfirmasi pembayaran donasi dari <strong><?= htmlspecialchars($donation['name'] ?? ''); ?></strong> sebesar <strong>Rp <?= number_format($donation['amount'], 0, ',', '.'); ?></strong>?</p>
            </div>
            <div class="modal-footer">
                <form id="confirmPaymentForm" method="POST" action="<?= BASE_URL; ?>/admin/confirm-payment">
                    <input type="hidden" name="donation_id" value="<?= $donation['id']; ?>">
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
                <p>Apakah Anda yakin akan menghapus data donasi dari <strong><?= htmlspecialchars($donation['name'] ?? ''); ?></strong> sebesar <strong>Rp <?= number_format($donation['amount'], 0, ',', '.'); ?></strong>?</p>
                <p class="text-danger"><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteDonationForm" method="POST" action="<?= BASE_URL; ?>/admin/delete-donation">
                    <input type="hidden" name="donation_id" value="<?= $donation['id']; ?>">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kirim Notifikasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="sendNotificationForm" method="POST" action="<?= BASE_URL; ?>/admin/send-notification">
                <div class="modal-body">
                    <input type="hidden" name="donation_id" value="<?= $donation['id']; ?>">
                    
                    <div class="form-group">
                        <label>Jenis Notifikasi</label>
                        <select class="form-control" name="notification_type" required>
                            <option value="email">Email</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="both">Email & WhatsApp</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Subjek</label>
                        <input type="text" class="form-control" name="subject" value="Status Donasi #<?= $donation['id']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Pesan</label>
                        <textarea class="form-control" name="message" rows="5" required>Terima kasih atas donasi Anda sebesar Rp <?= number_format($donation['amount'], 0, ',', '.'); ?> untuk kampanye "<?= $donation['campaign']['title']; ?>". 

Status donasi Anda saat ini: <?= ucfirst($donation['status']); ?>.

Salam,
Tim <?= APP_NAME; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Confirm payment button
        $('.confirm-payment').click(function() {
            $('#confirmPaymentModal').modal('show');
        });
        
        // Delete donation button
        $('.delete-donation').click(function() {
            $('#deleteDonationModal').modal('show');
        });
        
        // Send notification button
        $('#sendNotification').click(function() {
            $('#sendNotificationModal').modal('show');
        });
    });
</script>

<?php require_once BASEPATH . '/app/Views/admin/footer.php'; ?> 
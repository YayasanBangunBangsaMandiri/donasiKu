<?php require_once BASEPATH . '/app/Views/admin/header.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Pengaturan Aplikasi</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pengaturan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <?php if (isset($_SESSION['flash_message'])) : ?>
                        <div class="alert alert-<?= $_SESSION['flash_message']['type']; ?> alert-dismissible fade show">
                            <?= $_SESSION['flash_message']['message']; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="setting-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab" aria-controls="general" aria-selected="true">
                                <i class="fas fa-cog mr-1"></i> Umum
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="payment-tab" data-toggle="pill" href="#payment" role="tab" aria-controls="payment" aria-selected="false">
                                <i class="fas fa-credit-card mr-1"></i> Pembayaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="email-tab" data-toggle="pill" href="#email" role="tab" aria-controls="email" aria-selected="false">
                                <i class="fas fa-envelope mr-1"></i> Email
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="security-tab" data-toggle="pill" href="#security" role="tab" aria-controls="security" aria-selected="false">
                                <i class="fas fa-shield-alt mr-1"></i> Keamanan
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="setting-tabs-content">
                        <!-- General Settings -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <form method="POST" action="<?= BASE_URL; ?>/admin/save-settings" enctype="multipart/form-data">
                                <input type="hidden" name="settings_type" value="general">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="app_name">Nama Aplikasi</label>
                                            <input type="text" class="form-control" id="app_name" name="app_name" value="<?= APP_NAME; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="app_description">Deskripsi Aplikasi</label>
                                            <textarea class="form-control" id="app_description" name="app_description" rows="3"><?= APP_DESCRIPTION ?? ''; ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="contact_email">Email Kontak</label>
                                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= CONTACT_EMAIL ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="contact_phone">Telepon Kontak</label>
                                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?= CONTACT_PHONE ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="logo">Logo Aplikasi</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/*">
                                                    <label class="custom-file-label" for="logo">Pilih file</label>
                                                </div>
                                            </div>
                                            <?php if (file_exists(PUBLIC_PATH . '/assets/img/logo.png')) : ?>
                                                <div class="mt-2">
                                                    <img src="<?= BASE_URL; ?>/assets/img/logo.png" alt="Logo" style="max-height: 50px;">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="form-group">
                                            <label for="favicon">Favicon</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="favicon" name="favicon" accept="image/x-icon,image/png">
                                                    <label class="custom-file-label" for="favicon">Pilih file</label>
                                                </div>
                                            </div>
                                            <?php if (file_exists(PUBLIC_PATH . '/favicon.ico')) : ?>
                                                <div class="mt-2">
                                                    <img src="<?= BASE_URL; ?>/favicon.ico" alt="Favicon" style="max-height: 32px;">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="form-group">
                                            <label for="maintenance_mode">Mode Maintenance</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="maintenance_mode" name="maintenance_mode" value="1" <?= defined('MAINTENANCE_MODE') && MAINTENANCE_MODE ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="maintenance_mode">Aktifkan mode maintenance</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                            </form>
                        </div>
                        
                        <!-- Payment Settings -->
                        <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                            <form method="POST" action="<?= BASE_URL; ?>/admin/save-settings">
                                <input type="hidden" name="settings_type" value="payment">
                                <div class="card card-outline card-info mb-4">
                                    <div class="card-header">
                                        <h3 class="card-title">Pengaturan Midtrans</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="midtrans_client_key">Client Key</label>
                                            <input type="text" class="form-control" id="midtrans_client_key" name="midtrans_client_key" value="<?= MIDTRANS_CLIENT_KEY ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="midtrans_server_key">Server Key</label>
                                            <input type="text" class="form-control" id="midtrans_server_key" name="midtrans_server_key" value="<?= MIDTRANS_SERVER_KEY ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="midtrans_environment">Environment</label>
                                            <select class="form-control" id="midtrans_environment" name="midtrans_environment">
                                                <option value="sandbox" <?= (MIDTRANS_ENVIRONMENT ?? 'sandbox') == 'sandbox' ? 'selected' : ''; ?>>Sandbox (Testing)</option>
                                                <option value="production" <?= (MIDTRANS_ENVIRONMENT ?? '') == 'production' ? 'selected' : ''; ?>>Production</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card card-outline card-info mb-4">
                                    <div class="card-header">
                                        <h3 class="card-title">Metode Pembayaran</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="payment_bank_transfer" name="payment_methods[]" value="bank_transfer" <?= in_array('bank_transfer', ENABLED_PAYMENT_METHODS ?? []) ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="payment_bank_transfer">Transfer Bank</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="payment_credit_card" name="payment_methods[]" value="credit_card" <?= in_array('credit_card', ENABLED_PAYMENT_METHODS ?? []) ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="payment_credit_card">Kartu Kredit</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="payment_e_wallet" name="payment_methods[]" value="e_wallet" <?= in_array('e_wallet', ENABLED_PAYMENT_METHODS ?? []) ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="payment_e_wallet">E-Wallet</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                            </form>
                        </div>
                        
                        <!-- Email Settings -->
                        <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                            <form method="POST" action="<?= BASE_URL; ?>/admin/save-settings">
                                <input type="hidden" name="settings_type" value="email">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mail_host">SMTP Host</label>
                                            <input type="text" class="form-control" id="mail_host" name="mail_host" value="<?= MAIL_HOST ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="mail_port">SMTP Port</label>
                                            <input type="text" class="form-control" id="mail_port" name="mail_port" value="<?= MAIL_PORT ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="mail_username">SMTP Username</label>
                                            <input type="text" class="form-control" id="mail_username" name="mail_username" value="<?= MAIL_USERNAME ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="mail_password">SMTP Password</label>
                                            <input type="password" class="form-control" id="mail_password" name="mail_password" value="<?= MAIL_PASSWORD ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mail_encryption">Enkripsi</label>
                                            <select class="form-control" id="mail_encryption" name="mail_encryption">
                                                <option value="tls" <?= (MAIL_ENCRYPTION ?? '') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                                <option value="ssl" <?= (MAIL_ENCRYPTION ?? '') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                                <option value="none" <?= (MAIL_ENCRYPTION ?? '') == 'none' ? 'selected' : ''; ?>>None</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="mail_from_address">Alamat Pengirim</label>
                                            <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" value="<?= MAIL_FROM_ADDRESS ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="mail_from_name">Nama Pengirim</label>
                                            <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" value="<?= MAIL_FROM_NAME ?? ''; ?>">
                                        </div>
                                        <div class="form-group mt-4">
                                            <button type="button" class="btn btn-info" id="test_email">
                                                <i class="fas fa-paper-plane mr-1"></i> Kirim Email Test
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                            </form>
                        </div>
                        
                        <!-- Security Settings -->
                        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                            <form method="POST" action="<?= BASE_URL; ?>/admin/save-settings">
                                <input type="hidden" name="settings_type" value="security">
                                <div class="card card-outline card-danger mb-4">
                                    <div class="card-header">
                                        <h3 class="card-title">Pengaturan Keamanan</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="enable_recaptcha">Google reCAPTCHA</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="enable_recaptcha" name="enable_recaptcha" value="1" <?= ENABLE_RECAPTCHA ?? false ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="enable_recaptcha">Aktifkan Google reCAPTCHA untuk form</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="recaptcha_site_key">reCAPTCHA Site Key</label>
                                            <input type="text" class="form-control" id="recaptcha_site_key" name="recaptcha_site_key" value="<?= RECAPTCHA_SITE_KEY ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="recaptcha_secret_key">reCAPTCHA Secret Key</label>
                                            <input type="text" class="form-control" id="recaptcha_secret_key" name="recaptcha_secret_key" value="<?= RECAPTCHA_SECRET_KEY ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="enable_2fa">Autentikasi Dua Faktor (2FA)</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="enable_2fa" name="enable_2fa" value="1" <?= ENABLE_2FA ?? false ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="enable_2fa">Aktifkan 2FA untuk admin</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kirim Email Test</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="testEmailForm" method="POST" action="<?= BASE_URL; ?>/admin/test-email">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="test_email_to">Kirim ke Alamat Email</label>
                        <input type="email" class="form-control" id="test_email_to" name="test_email_to" value="<?= $_SESSION['user']['email']; ?>" required>
                    </div>
                    <p class="text-info">
                        <i class="fas fa-info-circle mr-1"></i> Email test akan dikirim menggunakan konfigurasi SMTP yang telah Anda atur.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Show filename in custom file input
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
        
        // Test email button
        $('#test_email').click(function() {
            $('#testEmailModal').modal('show');
        });
        
        // Active tab from URL hash
        let hash = window.location.hash;
        if (hash) {
            $('#setting-tabs a[href="' + hash + '"]').tab('show');
        }
        
        // Change hash on tab change
        $('#setting-tabs a').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash;
        });
    });
</script>

<?php require_once BASEPATH . '/app/Views/admin/footer.php'; ?>

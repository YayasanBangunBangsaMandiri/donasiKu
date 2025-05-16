<?php require_once BASEPATH . '/app/views/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Daftar Akun</h2>
                        <p class="text-muted">Bergabung dengan DonasiKu untuk memulai perjalanan berbagi kebaikan</p>
                    </div>

                    <?php if (isset($_SESSION['flash']['errors'])): ?>
                        <div class="alert alert-danger mb-4">
                            <h5><i class="fas fa-exclamation-circle me-2"></i> Ada kesalahan pada form:</h5>
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['flash']['errors'] as $field => $errors): ?>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/auth/doRegister" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-user text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="name" name="name" 
                                       placeholder="Masukkan nama lengkap Anda" required
                                       value="<?= $_SESSION['flash']['old']['name'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email" class="form-control border-start-0" id="email" name="email" 
                                       placeholder="Masukkan email Anda" required
                                       value="<?= $_SESSION['flash']['old']['email'] ?? '' ?>">
                            </div>
                            <small class="form-text text-muted">Kami tidak akan pernah membagikan email Anda.</small>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor HP (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-phone text-muted"></i>
                                </span>
                                <input type="tel" class="form-control border-start-0" id="phone" name="phone" 
                                       placeholder="Masukkan nomor HP Anda"
                                       value="<?= $_SESSION['flash']['old']['phone'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Kata Sandi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" id="password" name="password" 
                                       placeholder="Minimal 8 karakter" required>
                            </div>
                            <small class="form-text text-muted">Minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.</small>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" id="password_confirmation" name="password_confirmation" 
                                       placeholder="Ulangi kata sandi Anda" required>
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required>
                            <label class="form-check-label" for="agree_terms">
                                Saya setuju dengan <a href="<?= BASE_URL ?>/terms" class="text-decoration-none">syarat dan ketentuan</a> dan <a href="<?= BASE_URL ?>/privacy" class="text-decoration-none">kebijakan privasi</a>.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 mb-4">Daftar Sekarang</button>

                        <div class="text-center">
                            <p class="mb-0">Sudah punya akun? <a href="<?= BASE_URL ?>/auth/login" class="text-decoration-none">Masuk</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/views/partials/footer.php'; ?> 